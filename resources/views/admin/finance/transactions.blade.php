@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h2>Giao dịch thanh toán</h2>
    <p class="text-muted">Tra cứu thanh toán theo đơn hàng và cập nhật trạng thái COD.</p>

    <nav class="nav nav-pills mb-4" aria-label="Tài chính">
        <a class="nav-link" href="{{ route('admin.finance.index', request()->except('page', 'sort')) }}">Thống kê chỉ số</a>
        <a class="nav-link active" aria-current="page" href="{{ route('admin.finance.transactions', request()->except('page', 'sort')) }}">Giao dịch thanh toán</a>
    </nav>

    @if(session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="GET" action="{{ route('admin.finance.transactions') }}" class="card card-body mb-4">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="search">Tìm đơn hàng</label>
                <input id="search" name="search" class="form-control" maxlength="100" value="{{ $filters['search'] ?? '' }}" placeholder="Mã đơn, tên hoặc số điện thoại">
            </div>
            @foreach(['date_from' => 'Từ ngày tạo đơn', 'date_to' => 'Đến ngày tạo đơn'] as $field => $label)
                <div class="form-group col-md-4">
                    <label for="{{ $field }}">{{ $label }}</label>
                    <input type="date" id="{{ $field }}" name="{{ $field }}" class="form-control" value="{{ $filters[$field] ?? '' }}">
                </div>
            @endforeach
            @foreach(['min_amount' => 'Số tiền từ (đ)', 'max_amount' => 'Số tiền đến (đ)'] as $field => $label)
                <div class="form-group col-md-3">
                    <label for="{{ $field }}">{{ $label }}</label>
                    <input type="number" id="{{ $field }}" name="{{ $field }}" class="form-control" min="0" step="0.01" value="{{ $filters[$field] ?? '' }}" placeholder="Không giới hạn">
                </div>
            @endforeach
            @foreach(['gateway' => ['Phương thức', $methods], 'payment_status' => ['Trạng thái thanh toán', $statuses]] as $field => [$label, $options])
                <div class="form-group col-md-3">
                    <label for="{{ $field }}">{{ $label }}</label>
                    <select id="{{ $field }}" name="{{ $field }}" class="form-control">
                        <option value="">Tất cả</option>
                        @foreach($options as $value => $text)
                            <option value="{{ $value }}" @selected(($filters[$field] ?? '') === $value)>{{ $text }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            <div class="form-group col-md-4 mb-md-0">
                <label for="sort">Sắp xếp</label>
                <select name="sort" id="sort" class="form-control">
                    @foreach(['newest' => 'Mới nhất', 'oldest' => 'Cũ nhất', 'amount_desc' => 'Số tiền giảm dần', 'amount_asc' => 'Số tiền tăng dần'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-8 d-flex align-items-end">
                <button type="submit" class="btn btn-primary mr-2">Áp dụng bộ lọc</button>
                <a href="{{ route('admin.finance.transactions') }}" class="btn btn-outline-secondary">Xóa bộ lọc</a>
            </div>
        </div>
    </form>

    @php
        $colors = ['pending' => 'warning', 'initiated' => 'warning', 'paid' => 'success', 'failed' => 'danger', 'cancelled' => 'secondary', 'refund_pending' => 'info', 'refunded' => 'secondary'];
    @endphp
    <p class="text-muted">Có {{ number_format($orders->total()) }} đơn phù hợp bộ lọc. Số tiền bao gồm phí vận chuyển; ngày lọc là ngày tạo đơn.</p>
    <div class="card">
        <div class="card-header">
            <strong>Danh sách giao dịch ({{ number_format($orders->total()) }} đơn)</strong>
            <div class="small text-muted">COD: xác nhận thu tiền hoặc thất bại; đơn đã thu tiền có thể chuyển sang chờ hoàn tiền rồi xác nhận đã hoàn tiền.</div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-dark"><tr><th>Đơn hàng</th><th>Khách hàng</th><th>Phương thức</th><th class="text-right">Số tiền</th><th>Thanh toán</th><th>Cập nhật COD</th></tr></thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}">#{{ $order->id }}</a>
                                <small class="d-block text-muted text-nowrap">{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>{{ $order->name }}<small class="d-block text-muted">{{ $order->phone }}</small></td>
                            <td>{{ $methods[$order->gateway] ?? $order->gateway }}</td>
                            <td class="text-right text-nowrap">{{ number_format($order->total_price, 0, ',', '.') }} đ</td>
                            <td>
                                <span class="badge badge-{{ $colors[$order->payment_status] ?? 'secondary' }}">{{ $statuses[$order->payment_status] ?? $order->payment_status }}</span>
                                @if($order->paid_at)
                                    <small class="d-block text-muted">Thu: {{ \Carbon\Carbon::parse($order->paid_at)->format('d/m/Y H:i') }}</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $options = $codTransitions[$order->payment_status] ?? [];
                                    if ($order->status === 'cancelled' || in_array($order->shipping_status, ['cancelled', 'return', 'returned'], true)) {
                                        $options = array_filter($options, fn ($value) => $value === $order->payment_status || !in_array($value, ['pending', 'paid'], true));
                                    }
                                @endphp
                                @if($order->gateway === 'cod' && count($options) > 1)
                                    <form method="POST" action="{{ route('admin.finance.update-status', $order->id) }}" class="d-flex" style="min-width: 230px">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="current_payment_status" value="{{ $order->payment_status }}">
                                        <input type="hidden" name="current_order_status" value="{{ $order->status }}">
                                        <input type="hidden" name="current_payment_id" value="{{ $order->payment_id ?? 0 }}">
                                        <select name="payment_status" class="form-control form-control-sm mr-2" aria-label="Thanh toán đơn #{{ $order->id }}">
                                            @foreach($options as $value)
                                                <option value="{{ $value }}" @selected($order->payment_status === $value)>{{ $statuses[$value] }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm" aria-label="Lưu thanh toán đơn #{{ $order->id }}">Lưu</button>
                                    </form>
                                @elseif($order->gateway === 'momo')
                                    <small class="text-muted">Cập nhật từ MoMo</small>
                                @else
                                    <small class="text-muted">Không có thao tác</small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Không có đơn hàng phù hợp với bộ lọc.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="card-footer">{{ $orders->links('pagination::bootstrap-4') }}</div>
        @endif
    </div>
</div>
@endsection
