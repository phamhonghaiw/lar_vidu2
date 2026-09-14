@extends('layouts.admin')

@section('content')
<div class="container">
    <h2 class="mb-4">🔍 Chi tiết Đơn hàng #{{ $order->id }}</h2>

    <div class="card mb-4">
        <div class="card-body">
            <h5>🧾 Thông tin đơn hàng</h5>
            <p><strong>Khách hàng:</strong> {{ $order->name }}</p>
            <p><strong>Địa chỉ:</strong> {{ $order->address }}</p>
            <p><strong>Số điện thoại:</strong> {{ $order->phone }}</p>
            <p><strong>Trạng thái:</strong>
                @php
                    $statusMap = [
                        'pending' => 'Chờ tạo vận đơn',
                        'processing' => 'Đang tạo vận đơn',
                        'ready_to_pick' => 'Chờ lấy hàng',
                        'picking' => 'Đang lấy hàng',
                        'delivering' => 'Đang vận chuyển',
                        'delivered' => 'Đã giao hàng',
                        'return' => 'Đang hoàn hàng',
                        'cancelled' => 'Đã hủy'
                    ];
                    $statusClasses = [
                        'pending' => 'warning',
                        'processing' => 'warning',
                        'ready_to_pick' => 'info',
                        'picking' => 'primary',
                        'delivering' => 'primary',
                        'delivered' => 'success',
                        'return' => 'danger',
                        'cancelled' => 'danger',
                    ];
                    $payment = $order->paymentTransactions->first();
                    $paymentMap = ['pending' => 'Chờ thanh toán', 'initiated' => 'Đang chờ MoMo', 'paid' => 'Đã thanh toán', 'cod_ordered' => 'COD - chờ thu tiền', 'cod_paid' => 'COD - đã thu tiền', 'failed' => 'Thất bại', 'cancelled' => 'Đã hủy', 'refund_pending' => 'Chờ hoàn tiền'];
                    $paymentClasses = ['pending' => 'warning', 'initiated' => 'warning', 'paid' => 'success', 'cod_paid' => 'success', 'failed' => 'danger', 'cancelled' => 'danger', 'refund_pending' => 'info'];
                    $paymentStatus = $payment?->status ?? $order->status;
                @endphp
                <span class="badge badge-{{ $statusClasses[$order->shipping_status] ?? 'secondary' }}">{{ $statusMap[$order->shipping_status] ?? ucfirst($order->shipping_status) }}</span>
            </p>
            <p><strong>Thanh toán:</strong>
                <span class="badge badge-{{ $paymentClasses[$paymentStatus] ?? 'secondary' }}">{{ $paymentMap[$paymentStatus] ?? ($paymentStatus === 'cod' ? 'COD' : $paymentStatus) }}</span>
                @if($payment)
                    <span class="text-muted ml-2">{{ $payment->transaction_id ?? 'Chưa có mã giao dịch' }}</span>
                @endif
            </p>

            <p><strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <h5>📦 Danh sách sản phẩm:</h5>
    <table class="table table-bordered">
        <thead class="thead-light">
            <tr>
                <th>#</th>
                <th>Tên sản phẩm</th>
                <th>Đơn giá</th>
                <th>Số lượng</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product->name ?? 'Sản phẩm đã bị xóa' }}</td>
                    <td>{{ number_format($item->price, 0, ',', '.') }} đ</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->price * $item->quantity, 0, ',', '.') }} đ</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="text-right">
        <h5><strong>Tổng cộng: {{ number_format($order->total_price, 0, ',', '.') }} đ</strong></h5>
    </div>

    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary mt-3">← Quay lại danh sách</a>
</div>
@endsection
