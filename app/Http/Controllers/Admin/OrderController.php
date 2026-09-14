<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    // Hiển thị danh sách đơn hàng
    public function index(Request $request)
    {
        $paymentStatuses = ['pending', 'paid', 'cod_ordered', 'cod_paid', 'cancelled'];
        $shippingStatuses = ['pending', 'processing', 'ready_to_pick', 'picking', 'delivering', 'delivered', 'return', 'cancelled'];

        $validated = $request->validate([
            'status' => 'nullable|in:' . implode(',', $paymentStatuses),
            'shipping_status' => 'nullable|in:' . implode(',', $shippingStatuses),
        ]);

        $orders = Order::with(['paymentTransactions' => function ($query) {
                $query->latest();
            }])
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($validated['shipping_status'] ?? null, fn ($query, $status) => $query->where('shipping_status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.order.index', compact('orders'));
    }

    // Hiển thị chi tiết đơn hàng
    public function show($id)
    {
        $order = Order::with(['user', 'items.product', 'paymentTransactions' => function ($query) {
            $query->latest();
        }])->findOrFail($id);
        return view('admin.order.show', compact('order'));
    }
}
