@extends('layouts.user')

@section('title', 'Chi tiết đơn hàng')

@section('content')
<div class="container mt-4">
    @php
        $payment = $order->paymentTransactions->first();
        $paymentStatus = $payment ? ['pending' => 'Chờ thanh toán', 'initiated' => 'Đang chờ MoMo', 'paid' => 'Đã thanh toán', 'failed' => 'Thất bại'][$payment->status] ?? $payment->status : 'COD';
        $shippingStatus = ['pending' => 'Chờ tạo vận đơn', 'processing' => 'Đang tạo vận đơn', 'ready_to_pick' => 'Chờ lấy hàng', 'picking' => 'Đang lấy hàng', 'delivering' => 'Đang giao hàng', 'delivered' => 'Đã giao hàng', 'return' => 'Đang hoàn hàng'][$order->shipping_status] ?? $order->shipping_status;
        $paymentClass = ['paid' => 'success', 'failed' => 'danger', 'cancelled' => 'secondary', 'refund_pending' => 'info'][$payment?->status ?? 'cod'] ?? 'warning';
        $shippingClass = ['pending' => 'warning', 'processing' => 'warning', 'ready_to_pick' => 'info', 'picking' => 'primary', 'delivering' => 'primary', 'delivered' => 'success', 'return' => 'danger', 'cancelled' => 'danger'][$order->shipping_status] ?? 'secondary';
    @endphp
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h2 class="mb-1">Chi tiết đơn hàng #{{ $order->id }}</h2><small class="text-muted">Đặt lúc {{ $order->created_at->format('d/m/Y H:i:s') }}</small></div>
        <a href="{{ route('user.orders.index') }}" class="btn btn-outline-secondary btn-sm">Quay lại</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 mb-3 mb-md-0">
            <div class="card h-100 shadow-sm"><div class="card-body">
                <h5 class="mb-3">Thanh toán</h5>
                <p class="mb-2">Trạng thái: <span class="badge badge-{{ $paymentClass }}">{{ $paymentStatus }}</span></p>
                <p class="mb-2">Cổng: {{ strtoupper($payment->gateway ?? 'COD') }}</p>
                <p class="mb-2">Số tiền: <strong>{{ number_format($payment?->amount ?? $order->total_price, 0, ',', '.') }} đ</strong></p>
                <p class="mb-2">Mã giao dịch: {{ $payment->transaction_id ?? 'Chưa có' }}</p>
                <p class="mb-0">Thanh toán lúc: {{ $payment?->paid_at?->format('d/m/Y H:i:s') ?? 'Chưa thanh toán' }}</p>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 shadow-sm"><div class="card-body">
                <h5 class="mb-3">Vận chuyển GHN</h5>
                <p class="mb-2">Trạng thái: <span class="badge badge-{{ $shippingClass }}">{{ $shippingStatus }}</span></p>
                <p class="mb-2">Mã vận đơn: <strong>{{ $order->ghn_order_code ?? 'Chưa tạo' }}</strong></p>
                <p class="mb-2">Phí vận chuyển: {{ number_format($order->ghn_total_fee, 0, ',', '.') }} đ</p>
                <p class="mb-0">Cập nhật lúc: {{ $order->updated_at->format('d/m/Y H:i:s') }}</p>
            </div></div>
        </div>
    </div>

    <div class="card shadow-sm mb-4"><div class="card-body">
            <h5>Thông tin người nhận</h5>
            <div class="row"><div class="col-md-4">Họ tên: {{ $order->name }}</div><div class="col-md-4">Số điện thoại: {{ $order->phone }}</div><div class="col-md-4">Địa chỉ: {{ $order->address }}</div></div>
    </div></div>

    <div class="card shadow-sm"><div class="card-body">
            <h5>Danh sách sản phẩm</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tên sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Tổng</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product->name ?? 'Sản phẩm đã bị xóa' }}</td>
                            <td>{{ number_format($item->price, 0, ',', '.') }} đ</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->price * $item->quantity, 0, ',', '.') }} đ</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Tổng đơn hàng:</th>
                        <th>{{ number_format($order->total_price, 0, ',', '.') }} đ</th>
                    </tr>
                </tfoot>
            </table>
        </div></div>
</div>
@endsection
