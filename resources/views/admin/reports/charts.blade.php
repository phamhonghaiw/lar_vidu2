@extends('layouts.admin')

@section('title', 'Biểu đồ báo cáo doanh thu')

@section('content')
<style>
  .chart-wrap { min-height: 360px; }
  .chart-wrap canvas { width: 100% !important; height: 360px !important; }
</style>

<div class="container-fluid">
  <h2>Biểu đồ báo cáo doanh thu</h2>
  <nav class="nav nav-pills my-3" aria-label="Báo cáo">
    <a class="nav-link" href="{{ route('admin.reports.index') }}">Bảng số liệu</a>
    <a class="nav-link active" aria-current="page" href="{{ route('admin.reports.charts') }}">Biểu đồ</a>
  </nav>
  <p class="text-muted">Chỉ gồm đơn đã thanh toán, chưa hoàn tiền và không bị hủy hoặc hoàn hàng. Doanh thu tính theo ngày tạo đơn; số liệu theo danh mục không gồm phí vận chuyển.</p>
  <div id="report-chart-error" class="alert alert-warning d-none" role="alert">Không tải được thư viện biểu đồ. Bạn có thể xem số liệu tại trang <a href="{{ route('admin.reports.index') }}">Bảng số liệu</a>.</div>

  <div class="row">
    <div class="col-lg-6 mb-4">
      <div class="card shadow-sm h-100">
        <div class="card-header">Doanh thu theo danh mục</div>
        <div class="card-body chart-wrap"><canvas id="categoryRevenueChart"></canvas></div>
      </div>
    </div>

    <div class="col-lg-6 mb-4">
      <div class="card shadow-sm h-100">
        <div class="card-header">Doanh thu theo ngày (30 ngày)</div>
        <div class="card-body chart-wrap"><canvas id="revenueByDateChart"></canvas></div>
      </div>
    </div>

    <div class="col-lg-6 mb-4">
      <div class="card shadow-sm h-100">
        <div class="card-header">Doanh thu theo tháng (12 tháng)</div>
        <div class="card-body chart-wrap"><canvas id="revenueByMonthChart"></canvas></div>
      </div>
    </div>

    <div class="col-lg-6 mb-4">
      <div class="card shadow-sm h-100">
        <div class="card-header">Doanh thu theo năm</div>
        <div class="card-body chart-wrap"><canvas id="revenueByYearChart"></canvas></div>
      </div>
    </div>

    <div class="col-lg-12 mb-4">
      <div class="card shadow-sm">
        <div class="card-header">Doanh thu theo phương thức thanh toán</div>
        <div class="card-body chart-wrap"><canvas id="revenueByPaymentMethodChart"></canvas></div>
      </div>
    </div>
  </div>
</div>

<div id="report-chart-data" hidden data-chart-data="{{ json_encode([
  'catLabels' => $catLabels ?? [],
  'catRevenue' => $catRevenue ?? [],
  'revDateLabels' => $revDateLabels ?? [],
  'revDateData' => $revDateData ?? [],
  'revMonthLabels' => $revMonthLabels ?? [],
  'revMonthData' => $revMonthData ?? [],
  'revYearLabels' => $revYearLabels ?? [],
  'revYearData' => $revYearData ?? [],
  'paymentMethodLabels' => $paymentMethodLabels ?? [],
  'paymentMethodRevenue' => $paymentMethodRevenue ?? [],
]) }}"></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
window.addEventListener('DOMContentLoaded', () => {
  if (typeof Chart === 'undefined') {
    document.getElementById('report-chart-error').classList.remove('d-none');
    return;
  }
  // Dữ liệu Blade nằm trong HTML; phần này chỉ sử dụng JavaScript thuần.
  const reportData = JSON.parse(document.getElementById('report-chart-data').dataset.chartData);
  const catLabels  = reportData.catLabels;
  const catRevenue = reportData.catRevenue.map(Number);

  const revDateLabels = reportData.revDateLabels;
  const revDateData   = reportData.revDateData.map(Number);

  const revMonthLabels = reportData.revMonthLabels;
  const revMonthData   = reportData.revMonthData.map(Number);

  const revYearLabels = reportData.revYearLabels;
  const revYearData   = reportData.revYearData.map(Number);

  const payLabels  = reportData.paymentMethodLabels;
  const payRevenue = reportData.paymentMethodRevenue.map(Number);

  const mk = (el, type, labels, data, label) => new Chart(el, {
    type, data: { labels, datasets: [{ label, data, fill: type === 'line', tension: 0.3 }] },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
  });

  mk(document.getElementById('categoryRevenueChart'), 'bar',  catLabels,  catRevenue,  'Doanh thu (VNĐ)');
  mk(document.getElementById('revenueByDateChart'),  'line', revDateLabels, revDateData, 'Doanh thu (VNĐ)');
  mk(document.getElementById('revenueByMonthChart'), 'bar',  revMonthLabels, revMonthData, 'Doanh thu (VNĐ)');
  mk(document.getElementById('revenueByYearChart'),  'bar',  revYearLabels,  revYearData,  'Doanh thu (VNĐ)');
  new Chart(document.getElementById('revenueByPaymentMethodChart'), {
    type:'pie', data:{ labels: payLabels, datasets:[{ label:'Doanh thu (VNĐ)', data: payRevenue }] }, options:{ responsive:true, maintainAspectRatio:false }
  });
});
</script>
@endsection
