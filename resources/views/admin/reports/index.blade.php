@extends('layouts.admin')

@section('title', 'Báo cáo')

@section('content')
    <div class="container">
        <h1>Báo cáo</h1>

        <div class="mt-4">
            <h4>Tổng số đơn hàng: {{ $totalOrders }}</h4>
            <h4>Tổng số khách hàng: {{ $totalCustomers }}</h4>
        </div>

        <h3>Doanh thu theo từng danh mục</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Danh mục</th>
                    <th>Tổng doanh thu</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categoryRevenue as $revenue)
                    <tr>
                        {{-- Nếu controller có select tên danh mục: products.category_id, categories.name as category_name --}}
                        <td>{{ $revenue->category_name ?? $revenue->category_id }}</td>
                        <td>{{ number_format((float) $revenue->total_revenue, 0) }} VND</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h3>Doanh thu theo ngày</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Ngày</th>
                    <th>Tổng doanh thu</th>
                </tr>
            </thead>
            <tbody>
                @foreach($revenueByDate as $revenue)
                    <tr>
                        {{-- date có thể là string từ SELECT DATE(created_at) --}}
                        <td>
                            @php
                                try {
                                    $d = \Carbon\Carbon::parse($revenue->date)->format('d/m/Y');
                                } catch (\Exception $e) {
                                    $d = $revenue->date;
                                }
                            @endphp
                            {{ $d }}
                        </td>
                        <td>{{ number_format((float) $revenue->total_revenue, 0) }} VND</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h3>Doanh thu theo tháng</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Tháng</th>
                    <th>Tổng doanh thu</th>
                </tr>
            </thead>
            <tbody>
                @foreach($revenueByMonth as $revenue)
                    <tr>
                        {{-- Nếu controller có cả year + month: select YEAR(created_at) as year, MONTH(created_at) as month --}}
                        <td>
                            @php
                                $m = str_pad($revenue->month ?? '', 2, '0', STR_PAD_LEFT);
                                $label = isset($revenue->year) ? ($m . '/' . $revenue->year) : $m;
                            @endphp
                            {{ $label }}
                        </td>
                        <td>{{ number_format((float) $revenue->total_revenue, 0) }} VND</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h3>Doanh thu theo năm</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Năm</th>
                    <th>Tổng doanh thu</th>
                </tr>
            </thead>
            <tbody>
                @foreach($revenueByYear as $revenue)
                    <tr>
                        <td>{{ $revenue->year }}</td>
                        <td>{{ number_format((float) $revenue->total_revenue, 0) }} VND</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
