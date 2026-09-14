{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')

@section('content')
<div class="container py-3">
    <h1 class="mb-4">Dashboard Thống Kê</h1>

    <!-- Thống kê tổng quan chung -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body">
                    <h6 class="card-title text-uppercase fw-bold opacity-75">Tổng Thành Viên</h6>
                    <h2 class="display-6 fw-bold mb-0">{{ number_format($totalUsers) }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body">
                    <h6 class="card-title text-uppercase fw-bold opacity-75">Tổng Đơn Hàng</h6>
                    <h2 class="display-6 fw-bold mb-0">{{ number_format($totalOrders) }}</h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-dark text-white h-100">
                <div class="card-body">
                    <h6 class="card-title text-uppercase fw-bold opacity-75">Tổng Doanh Thu</h6>
                    <h2 class="display-6 fw-bold mb-0">{{ number_format($totalRevenue, 0, ',', '.') }} VNĐ</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Báo cáo 7 ngày gần nhất -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-primary">📊 Báo Cáo 7 Ngày Gần Nhất</h5>
        </div>
        <div class="card-body">
            <!-- Thống kê tổng 7 ngày -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded text-center">
                        <span class="text-muted small text-uppercase d-block mb-1">Người đăng ký mới (7 ngày)</span>
                        <h3 class="fw-bold text-info mb-0">{{ number_format($totalNewUsers7Days) }}</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded text-center">
                        <span class="text-muted small text-uppercase d-block mb-1">Đơn hàng mới (7 ngày)</span>
                        <h3 class="fw-bold text-warning mb-0">{{ number_format($totalNewOrders7Days) }}</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded text-center">
                        <span class="text-muted small text-uppercase d-block mb-1">Doanh thu (7 ngày)</span>
                        <h3 class="fw-bold text-success mb-0">{{ number_format($totalRevenue7Days, 0, ',', '.') }} VNĐ</h3>
                    </div>
                </div>
            </div>

            <!-- Biểu đồ 7 ngày -->
            <div class="row mb-4">
                <div class="col-lg-8 mb-3 mb-lg-0">
                    <div class="p-3 border rounded">
                        <h6 class="fw-bold mb-3 text-secondary">Doanh Thu Theo Ngày (7 ngày)</h6>
                        <canvas id="revenueChart" style="max-height: 280px;"></canvas>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="p-3 border rounded">
                        <h6 class="fw-bold mb-3 text-secondary">Đăng Ký & Đơn Hàng Mới (7 ngày)</h6>
                        <canvas id="activityChart" style="max-height: 280px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Bảng chi tiết từng ngày trong 7 ngày -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ngày</th>
                            <th class="text-center">Số người đăng ký mới</th>
                            <th class="text-center">Số đơn hàng mới</th>
                            <th class="text-end">Doanh thu theo ngày</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report7Days as $day)
                            <tr>
                                <td>
                                    <span class="fw-semibold">{{ $day['date_label'] }}</span>
                                    <small class="text-muted">({{ $day['full_date'] }})</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info text-dark px-3 py-2 fs-6">{{ $day['new_users'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark px-3 py-2 fs-6">{{ $day['new_orders'] }}</span>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    {{ number_format($day['revenue'], 0, ',', '.') }} VNĐ
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const reportData = {!! json_encode(array_values($report7Days->toArray())) !!};

    const labels = reportData.map(item => item.date_label);
    const revenues = reportData.map(item => item.revenue);
    const newUsers = reportData.map(item => item.new_users);
    const newOrders = reportData.map(item => item.new_orders);

    // Biểu đồ Doanh thu
    const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctxRevenue, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Doanh Thu (VNĐ)',
                data: revenues,
                backgroundColor: 'rgba(25, 135, 84, 0.7)',
                borderColor: '#198754',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
                        }
                    }
                }
            }
        }
    });

    // Biểu đồ Đăng ký mới & Đơn hàng mới
    const ctxActivity = document.getElementById('activityChart').getContext('2d');
    new Chart(ctxActivity, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Đăng Ký Mới',
                    data: newUsers,
                    borderColor: '#0dcaf0',
                    backgroundColor: 'rgba(13, 202, 240, 0.1)',
                    tension: 0.3,
                    fill: true,
                },
                {
                    label: 'Đơn Hàng Mới',
                    data: newOrders,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.3,
                    fill: true,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        precision: 0
                    }
                }
            }
        }
    });
});
</script>
@endsection
