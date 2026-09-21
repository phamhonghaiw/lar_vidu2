@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h2>Thống kê tài chính</h2>
    <p class="text-muted">Tổng hợp giá trị thanh toán theo trạng thái và phương thức.</p>

    <nav class="nav nav-pills mb-4" aria-label="Tài chính">
        <a class="nav-link active" aria-current="page" href="{{ route('admin.finance.index', request()->except('page', 'sort')) }}">Thống kê chỉ số</a>
        <a class="nav-link" href="{{ route('admin.finance.transactions', request()->except('page', 'sort')) }}">Giao dịch thanh toán</a>
    </nav>

    @if(session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="GET" action="{{ route('admin.finance.index') }}" class="card card-body mb-4">
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
            <div class="col-12 d-flex align-items-end">
                <button type="submit" class="btn btn-primary mr-2">Áp dụng bộ lọc</button>
                <a href="{{ route('admin.finance.index') }}" class="btn btn-outline-secondary">Xóa bộ lọc</a>
            </div>
        </div>
    </form>

    <p class="text-muted">Có {{ number_format($summary->order_count) }} đơn phù hợp. Số tiền bao gồm phí vận chuyển, thống kê theo ngày tạo đơn trên toàn bộ kết quả lọc.</p>
    <div class="row">
        <div class="col-md-6 col-xl-3 mb-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted mb-2">Tổng giá trị đơn hàng</div>
                <h4>{{ number_format($summary->total_amount, 0, ',', '.') }} đ</h4>
                <small>{{ number_format($summary->order_count) }} đơn, bao gồm đơn đã hủy</small>
            </div></div>
        </div>
        @php
            $colors = ['pending' => 'warning', 'initiated' => 'warning', 'paid' => 'success', 'failed' => 'danger', 'cancelled' => 'secondary', 'refund_pending' => 'info', 'refunded' => 'secondary'];
        @endphp
        @foreach($statuses as $status => $label)
            <div class="col-md-6 col-xl-3 mb-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted mb-2">{{ $label }}</div>
                    <h4 class="text-{{ $colors[$status] }}">{{ number_format($statusTotals->get($status)?->total_amount ?? 0, 0, ',', '.') }} đ</h4>
                    <small>{{ number_format($statusTotals->get($status)?->order_count ?? 0) }} đơn</small>
                </div></div>
            </div>
        @endforeach
    </div>

    <div class="card mb-4">
        <div class="card-header font-weight-bold">Thống kê theo phương thức</div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>Phương thức</th><th>Số đơn</th><th class="text-right">Tổng giá trị</th><th class="text-right">Đã thanh toán</th></tr></thead>
                <tbody>
                    @foreach($methods as $method => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td>{{ number_format($methodTotals->get($method)?->order_count ?? 0) }}</td>
                            <td class="text-right">{{ number_format($methodTotals->get($method)?->total_amount ?? 0, 0, ',', '.') }} đ</td>
                            <td class="text-right">{{ number_format($methodTotals->get($method)?->paid_amount ?? 0, 0, ',', '.') }} đ</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
