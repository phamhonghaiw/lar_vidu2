@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">🧾 Quản lý Đơn hàng</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.orders.index') }}" class="card card-body mb-4">
        <div class="form-row align-items-end">
            <div class="form-group col-md-5 mb-md-0">
                <label for="status">Trạng thái thanh toán</label>
                <select name="status" id="status" class="form-control">
                    <option value="">Tất cả trạng thái</option>
                    @foreach(['pending' => 'Chờ thanh toán', 'paid' => 'Đã thanh toán', 'cod_ordered' => 'Đã đặt COD', 'cancelled' => 'Đã hủy'] as $key => $label)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-5 mb-md-0">
                <label for="shipping_status">Trạng thái giao hàng</label>
                <select name="shipping_status" id="shipping_status" class="form-control">
                    <option value="">Tất cả trạng thái</option>
                    @foreach(['pending' => 'Chờ tạo vận đơn', 'processing' => 'Đang tạo vận đơn', 'ready_to_pick' => 'Chờ lấy hàng', 'picking' => 'Đang lấy hàng', 'delivering' => 'Đang giao hàng', 'delivered' => 'Đã giao hàng', 'return' => 'Đang hoàn hàng', 'cancelled' => 'Đã hủy'] as $key => $label)
                        <option value="{{ $key }}" {{ request('shipping_status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2 mb-md-0">
                <button type="submit" class="btn btn-primary btn-block">Lọc đơn hàng</button>
            </div>
        </div>
        @if(request()->hasAny(['status', 'shipping_status']))
            <a href="{{ route('admin.orders.index') }}" class="small mt-3">Xóa bộ lọc</a>
        @endif
    </form>

    @if($orders->count() > 0)
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>SĐT</th>
                    <th>Địa chỉ</th>
                    <th>Tổng tiền</th>
                    <th>Thanh toán</th>
                    <th>Giao hàng</th>
                    <th>Ngày đặt</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $index => $order)
                <tr>
                    <td>{{ $orders->firstItem() + $index }}</td>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->name }}</td>
                    <td>{{ $order->phone }}</td>
                    <td>{{ $order->address }}</td>
                    <td>{{ number_format($order->total_price, 0, ',', '.') }} đ</td>
                    <td>
                        @php
                            $payment = $order->paymentTransactions->first();
                            $paymentLabels = ['pending' => 'Chờ thanh toán', 'initiated' => 'Đang chờ MoMo', 'paid' => 'Đã thanh toán', 'cod_ordered' => 'COD - chờ thu tiền', 'cod_paid' => 'COD - đã thu tiền', 'failed' => 'Thất bại', 'cancelled' => 'Đã hủy', 'refund_pending' => 'Chờ hoàn tiền'];
                            $paymentClasses = ['pending' => 'warning', 'initiated' => 'warning', 'paid' => 'success', 'cod_paid' => 'success', 'failed' => 'danger', 'cancelled' => 'danger', 'refund_pending' => 'info'];
                            $paymentStatus = $payment?->status ?? $order->status;
                        @endphp
                        <span class="badge badge-{{ $paymentClasses[$paymentStatus] ?? 'secondary' }}">{{ $paymentLabels[$paymentStatus] ?? ($paymentStatus === 'pending' && $payment?->gateway === 'cod' ? 'COD - chờ thu tiền' : $paymentStatus) }}</span>
                    </td>
                    <td>
                        @php
                            $shippingStatuses = [
                                'pending' => ['label' => 'Chờ tạo vận đơn', 'class' => 'warning'],
                                'processing' => ['label' => 'Đang tạo vận đơn', 'class' => 'warning'],
                                'ready_to_pick' => ['label' => 'Chờ lấy hàng', 'class' => 'info'],
                                'picking' => ['label' => 'Đang lấy hàng', 'class' => 'primary'],
                                'delivering' => ['label' => 'Đang vận chuyển', 'class' => 'primary'],
                                'delivered' => ['label' => 'Đã giao hàng', 'class' => 'success'],
                                'return' => ['label' => 'Đang hoàn hàng', 'class' => 'danger'],
                                'cancelled' => ['label' => 'Đã hủy', 'class' => 'danger'],
                            ];
                        @endphp
                        <span class="badge badge-{{ $shippingStatuses[$order->shipping_status]['class'] ?? 'secondary' }}">
                            {{ $shippingStatuses[$order->shipping_status]['label'] ?? $order->shipping_status }}
                        </span>
                        <div class="small text-muted mt-1">Cập nhật tự động từ GHN</div>
                    </td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-info btn-sm">🔍 Chi tiết</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-3">{{ $orders->links() }}</div>
    @else
        <p>Không có đơn hàng nào.</p>
    @endif
</div>
@endsection
