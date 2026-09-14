<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class GHNWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        // Ghi log để debug payload thực tế từ GHN
        Log::info('GHN Webhook Payload:', $payload);

        $orderCode = $payload['OrderCode'] ?? null;
        $status    = $payload['Status'] ?? null; // ready_to_pick, picking, delivering, delivered, return,...

        if (!$orderCode || !$status) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $order = Order::where('ghn_order_code', $orderCode)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order->update(['shipping_status' => $status]);

        if ($status === 'delivered') {
            $order->paymentTransactions()
                ->where('gateway', 'cod')
                ->where('status', 'pending')
                ->update([
                    'status' => 'paid',
                    'message' => 'Đã thu tiền khi giao hàng',
                    'paid_at' => Carbon::now(),
                ]);

            if ($order->status === 'cod_ordered') {
                $order->update(['status' => 'cod_paid']);
            }
        }

        return response()->json(['status' => 'success'], 200);
    }
}