<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Services\GHNService;
use App\Services\GHNOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    // ==========================================
    // 1. CÁC VIEW HIỂN THỊ ĐƠN HÀNG & THANH TOÁN
    // ==========================================

    public function index()
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('user.cart.index')->with('error', 'Giỏ hàng đang trống.');
        }

        $totalPrice = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);

        return view('user.payment.index', compact('cart', 'totalPrice'));
    }

    public function orderHistory()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['items.product', 'paymentTransactions' => function ($query) {
                $query->latest();
            }])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('user.payment.order', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403);
        }

        $order->load(['items.product', 'paymentTransactions' => function ($query) {
            $query->latest();
        }]);

        return view('user.payment.show', compact('order'));
    }

    public function cancel(Order $order, GHNService $ghn)
    {
        abort_unless($order->user_id === Auth::id(), 403);

        $allowedStatuses = ['pending', 'ready_to_pick'];
        if (!in_array($order->shipping_status, $allowedStatuses, true)) {
            return back()->with('error', 'Đơn hàng không còn ở trạng thái có thể hủy.');
        }

        if ($order->ghn_order_code) {
            $response = $ghn->cancelOrder([$order->ghn_order_code]);
            if (($response['code'] ?? null) !== 200) {
                return back()->with('error', 'GHN không cho phép hủy vận đơn này.');
            }
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status' => 'cancelled',
                'shipping_status' => 'cancelled',
            ]);

            $order->paymentTransactions()
                ->whereIn('status', ['pending', 'initiated'])
                ->update(['status' => 'cancelled']);

            $order->paymentTransactions()
                ->where('status', 'paid')
                ->update(['status' => 'refund_pending']);
        });

        return back()->with('success', 'Đơn hàng đã được hủy.');
    }

    // ==========================================
    // 3. XỬ LÝ ĐẶT HÀNG (PROCESS PAYMENT)
    // ==========================================

    public function processPayment(Request $request, GHNService $ghn, GHNOrderService $ghnOrders)
    {
        $request->validate([
            'name'           => 'required|string|max:100',
            'phone'          => ['required', 'regex:/^0\d{9}$/'],
            'address'        => 'required|string|max:255',
            'to_district_id' => 'required|integer',
            'to_ward_code'   => 'required|string',
            'payment_method' => 'required|in:cod,momo',
        ]);

        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('user.cart.index')->with('error', 'Không thể thanh toán vì giỏ hàng trống.');
        }

        // 1. Tính tổng tiền hàng và tổng khối lượng sản phẩm
        $subtotal = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
            $totalWeight = collect($cart)->sum(
                fn($item) => $ghn->productWeight() * (int) $item['quantity']
        );

        // 2. Tính lại phí ship chuẩn xác từ GHN trên server
        $feeResponse = $ghn->calculateFee(array_merge([
            'from_district_id' => (int) config('services.ghn.from_district_id'),
            'to_district_id'   => (int) $request->to_district_id,
            'to_ward_code'     => (string) $request->to_ward_code,
        ], $ghn->packageParameters($totalWeight)));

        $shippingFee = (isset($feeResponse['code']) && $feeResponse['code'] == 200)
            ? (int) $feeResponse['data']['total']
            : 0;

        // Tổng thanh toán = Tiền hàng + Phí ship
        $finalTotal = $subtotal + $shippingFee;

        // 3. Tạo đơn hàng và chi tiết đơn hàng trong Database
        $order = DB::transaction(function () use ($request, $shippingFee, $finalTotal, $cart) {
            $order = Order::create([
                'user_id'         => Auth::id(),
                'name'            => $request->name,
                'address'         => $request->address,
                'phone'           => $request->phone,
                'total_price'     => $finalTotal,
                'status'          => 'pending',
                'to_district_id'  => (int) $request->to_district_id,
                'to_ward_code'    => (string) $request->to_ward_code,
                'ghn_total_fee'   => $shippingFee,
                'shipping_status' => 'pending',
            ]);

            foreach ($cart as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                ]);
            }

            return $order;
        });

        // Xóa session giỏ hàng
        session()->forget('cart');

        // 4. Phân luồng thanh toán
        if ($request->payment_method === 'momo') {
            PaymentTransaction::create([
                'order_id' => $order->id,
                'gateway' => 'momo',
                'amount' => $order->total_price,
                'status' => 'pending',
            ]);

            return redirect()->route('user.orders.momo.start', $order);
        }

        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'cod',
            'amount' => $order->total_price,
            'status' => 'pending',
            'message' => 'Thanh toán khi nhận hàng',
        ]);

        // --- NHÁNH COD: TẠO VẬN ĐƠN GHN NGAY LẬP TỨC ---
        $order->load('items.product');
        $ghnOrderResponse = $ghnOrders->create($order);

        if (($ghnOrderResponse['code'] ?? null) == 200 && !empty($ghnOrderResponse['data']['order_code'])) {
            $order->update([
                'status'          => 'cod_ordered',
                'ghn_order_code'  => $ghnOrderResponse['data']['order_code'],
                'shipping_status' => 'ready_to_pick',
            ]);

            return redirect()->route('user.orders.index')
                ->with('success', 'Đặt hàng thành công! Mã vận đơn GHN: ' . $ghnOrderResponse['data']['order_code']);
        }

        Log::error('GHN COD Order Failed: ', $ghnOrderResponse ?? []);
        $order->update(['status' => 'cod_ordered']);

        return redirect()->route('user.orders.index')
            ->with('warning', 'Đặt hàng thành công nhưng chưa thể tạo vận đơn GHN tự động.');
    }

}