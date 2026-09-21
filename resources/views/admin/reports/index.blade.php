@extends('layouts.admin')

@section('title', 'Báo cáo doanh thu')

@section('content')
<div class="container-fluid">
    <h2>Báo cáo doanh thu</h2>
    <nav class="nav nav-pills my-3" aria-label="Báo cáo">
        <a class="nav-link active" aria-current="page" href="{{ route('admin.reports.index') }}">Bảng số liệu</a>
        <a class="nav-link" href="{{ route('admin.reports.charts') }}">Biểu đồ</a>
    </nav>
    <p class="text-muted">Doanh thu tính theo ngày tạo đơn, chỉ gồm đơn đã thanh toán, chưa hoàn tiền và không bị hủy hoặc hoàn hàng.</p>

    <div class="row mb-4">
        <div class="col-md-4 mb-3"><div class="card card-body h-100">
            <span class="text-muted">Tổng số đơn hàng</span>
            <h3 class="mb-0">{{ number_format($totalOrders) }}</h3>
        </div></div>
        <div class="col-md-4 mb-3"><div class="card card-body h-100">
            <span class="text-muted">Tổng số khách hàng</span>
            <h3 class="mb-0">{{ number_format($totalCustomers) }}</h3>
        </div></div>
        <div class="col-md-4 mb-3"><div class="card card-body h-100">
            <span class="text-muted">Tổng doanh thu (gồm phí vận chuyển)</span>
            <h3 class="mb-0 text-success">{{ number_format($totalRevenue, 0, ',', '.') }} đ</h3>
        </div></div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Doanh thu theo danh mục</strong>
            <div class="small text-muted">Tính theo giá sản phẩm khi đặt hàng, không gồm phí vận chuyển.</div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead><tr><th>Danh mục</th><th class="text-right">Số lượng bán</th><th class="text-right">Doanh thu</th></tr></thead>
                <tbody>
                    @forelse($categoryRevenue as $revenue)
                        <tr>
                            <td>{{ $revenue->category_name ?? ('Danh mục #'.$revenue->category_id) }}</td>
                            <td class="text-right">{{ number_format($revenue->total_qty) }}</td>
                            <td class="text-right">{{ number_format($revenue->total_revenue, 0, ',', '.') }} đ</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">Chưa có doanh thu.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach([
        ['Doanh thu theo ngày', 'Ngày', 'date', $revenueByDate, 'd/m/Y'],
        ['Doanh thu theo tháng', 'Tháng', 'month', $revenueByMonth, 'm/Y'],
        ['Doanh thu theo năm', 'Năm', 'year', $revenueByYear, null],
    ] as [$title, $label, $field, $rows, $format])
        <div class="card mb-4">
            <div class="card-header font-weight-bold">{{ $title }}</div>
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead><tr><th>{{ $label }}</th><th class="text-right">Số đơn đã thanh toán</th><th class="text-right">Doanh thu</th></tr></thead>
                    <tbody>
                        @forelse($rows as $revenue)
                            <tr>
                                <td>{{ $format ? \Carbon\Carbon::parse($revenue->{$field}.($field === 'month' ? '-01' : ''))->format($format) : $revenue->{$field} }}</td>
                                <td class="text-right">{{ number_format($revenue->order_count) }}</td>
                                <td class="text-right">{{ number_format($revenue->total_revenue, 0, ',', '.') }} đ</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">Chưa có doanh thu.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</div>
@endsection
