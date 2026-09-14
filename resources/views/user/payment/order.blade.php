@extends('layouts.user')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <h2 class="mb-1">Lịch sử đơn hàng</h2>
        <p class="text-muted mb-0">Theo dõi thanh toán và vận chuyển của bạn.</p>
    </div>

    @forelse($orders as $order)
        @php
            $payment = $order->paymentTransactions->first();
            $paymentStatus = $payment ? ($payment->status === 'pending' && $payment->gateway === 'cod' ? 'COD - chờ thu tiền' : (['pending' => 'Chờ thanh toán', 'initiated' => 'Đang chờ MoMo', 'paid' => 'Đã thanh toán', 'failed' => 'Thất bại', 'cancelled' => 'Đã hủy'][$payment->status] ?? $payment->status)) : 'COD';
            $shippingStatus = ['pending' => 'Chờ tạo vận đơn', 'processing' => 'Đang tạo vận đơn', 'ready_to_pick' => 'Chờ lấy hàng', 'picking' => 'Đang lấy hàng', 'delivering' => 'Đang giao hàng', 'delivered' => 'Đã giao hàng', 'return' => 'Đang hoàn hàng'][$order->shipping_status] ?? $order->shipping_status;
            $paymentClass = ['paid' => 'success', 'failed' => 'danger', 'cancelled' => 'secondary', 'refund_pending' => 'info'][$payment?->status ?? 'cod'] ?? 'warning';
            $shippingClass = ['pending' => 'warning', 'processing' => 'warning', 'ready_to_pick' => 'info', 'picking' => 'primary', 'delivering' => 'primary', 'delivered' => 'success', 'return' => 'danger', 'cancelled' => 'danger'][$order->shipping_status] ?? 'secondary';
        @endphp
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-lg-3 mb-3 mb-lg-0">
                        <div class="text-muted small">ĐƠN HÀNG</div>
                        <h5 class="mb-1">#{{ $order->id }}</h5>
                        <small class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</small>
                    </div>
                    <div class="col-lg-3 mb-3 mb-lg-0">
                        <div class="text-muted small">THANH TOÁN</div>
                        <span class="badge badge-{{ $paymentClass }}">{{ $paymentStatus }}</span>
                        <div class="mt-1 font-weight-bold">{{ number_format($payment?->amount ?? $order->total_price, 0, ',', '.') }} đ</div>
                    </div>
                    <div class="col-lg-3 mb-3 mb-lg-0">
                        <div class="text-muted small">VẬN CHUYỂN GHN</div>
                        <span class="badge badge-{{ $shippingClass }}">{{ $shippingStatus }}</span>
                        <div class="mt-1 small">Mã vận đơn: <strong>{{ $order->ghn_order_code ?? 'Chưa tạo' }}</strong></div>
                    </div>
                    <div class="col-lg-3 text-lg-right">
                        @if($order->status === 'pending')
                            <a href="{{ route('user.orders.momo.pay', $order) }}" class="btn btn-success btn-sm mb-2">Thanh toán MoMo</a><br>
                        @endif
                        @if(in_array($order->shipping_status, ['pending', 'ready_to_pick'], true))
                            <form method="POST" action="{{ route('user.orders.cancel', $order) }}" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này?');">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm mb-2">Hủy đơn</button>
                            </form>
                        @endif
                        <button class="btn btn-outline-primary btn-sm" type="button" data-toggle="collapse" data-target="#detail{{ $order->id }}" aria-expanded="false">Xem chi tiết</button>
                    </div>
                </div>
            </div>
            <div class="collapse" id="detail{{ $order->id }}">
                <div class="card-body border-top bg-light">
                    <div class="row small mb-3">
                        <div class="col-md-4"><strong>Người nhận:</strong> {{ $order->name }}</div>
                        <div class="col-md-4"><strong>Số điện thoại:</strong> {{ $order->phone }}</div>
                        <div class="col-md-4"><strong>Địa chỉ:</strong> {{ $order->address }}</div>
                    </div>
                    <div class="row small mb-3">
                        <div class="col-md-4"><strong>Phí GHN:</strong> {{ number_format($order->ghn_total_fee, 0, ',', '.') }} đ</div>
                        <div class="col-md-4"><strong>Cập nhật đơn:</strong> {{ $order->updated_at->format('d/m/Y H:i:s') }}</div>
                        @if($payment)
                            <div class="col-md-4"><strong>Mã giao dịch:</strong> {{ $payment->transaction_id ?? 'Chưa có' }}</div>
                        @endif
                    </div>
                    <table class="table table-sm table-bordered bg-white mb-0">
                        <thead><tr><th>Sản phẩm</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead>
                        <tbody>
                        @foreach($order->items as $item)
                            <tr><td>{{ $item->product->name ?? 'Sản phẩm không tồn tại' }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->price, 0, ',', '.') }} đ</td><td>{{ number_format($item->price * $item->quantity, 0, ',', '.') }} đ</td></tr>
                        @endforeach
                        </tbody>
                        <tfoot><tr><th colspan="3" class="text-right">Tổng đơn hàng</th><th>{{ number_format($order->total_price, 0, ',', '.') }} đ</th></tr></tfoot>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-info">Bạn chưa có đơn hàng nào.</div>
    @endforelse

    <div class="mt-3">{{ $orders->links() }}</div>
</div>
@endsection
