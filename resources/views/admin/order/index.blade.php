@extends('layouts.admin')
@section('title', 'Quản lý đơn hàng')
@section('body-class', 'orders-screen')

@push('styles')
<style>
.orders-screen .admin-shell { height: 100vh; overflow: hidden; }
.orders-screen .content { padding: 0; min-width: 0; min-height: 0; display: flex; flex-direction: column; }
.order-workspace { display: flex; flex-direction: column; flex: 1; min-height: 0; }
.order-toolbar { display: flex; align-items: center; flex-wrap: wrap; gap: 9px; padding: 13px 16px; background: white; border-bottom: 1px solid #e4e8ee; }
.orders-screen .content .order-heading { font-size: 14px; font-weight: 700; margin: 0 8px 0 0; white-space: nowrap; }
.order-icon { width: 15px; height: 15px; fill: none; stroke: currentColor; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; vertical-align: middle; }
.order-control, .order-tool { min-height: 31px; border: 1px solid #dce2e9; border-radius: 3px; background: white; color: #4d5d70; font-size: 11px; padding: 5px 9px; }
.order-control:focus, .order-tool:focus-visible, .order-search input:focus { outline: 2px solid #8db8ff; outline-offset: 1px; }
.order-tool { display: inline-flex; align-items: center; justify-content: center; gap: 6px; cursor: pointer; white-space: nowrap; }
.order-tool:hover { background: #f0f5fc; color: #286ce7; text-decoration: none; }
.order-tool:disabled { cursor: default; opacity: .45; }
.order-pager { display: flex; align-items: center; gap: 7px; color: #718094; font-size: 11px; white-space: nowrap; }
.order-pager .order-tool { padding: 5px; min-width: 24px; }
.order-search { display: flex; flex: 1; min-width: 160px; max-width: 330px; align-items: center; border: 1px solid #dce2e9; border-radius: 3px; margin: 0; }
.order-search input { border: 0; background: transparent; padding: 7px 9px; width: 100%; min-width: 0; font-size: 11px; }
.order-search button { color: #8391a4; border: 0; background: transparent; padding: 5px 9px; }
.order-export { background: #16a264; color: white; border-color: #16a264; }
.order-export:hover { background: #118354; color: white; }
.order-filters { background: white; border-bottom: 1px solid #e4e8ee; padding: 0 16px; }
.order-filters summary { cursor: pointer; color: #66768a; padding: 10px 0; font-size: 11px; width: fit-content; }
.order-filter-grid { display: grid; grid-template-columns: repeat(4, minmax(130px, 1fr)) auto; gap: 12px; padding-bottom: 14px; align-items: end; }
.order-filter-grid label { display: block; font-size: 11px; color: #738195; margin: 0 0 5px; }
.order-filter-grid .order-control { width: 100%; }
.order-filter-actions { display: flex; gap: 6px; }
.order-apply { background: #397df0; color: white; border-color: #397df0; }
.order-tabs { display: flex; flex-shrink: 0; gap: 3px; padding: 0 14px; background: white; border-bottom: 1px solid #dce2e9; overflow-x: auto; }
.order-tab { --tab-color: #387bf0; display: flex; align-items: center; gap: 6px; padding: 13px 10px 11px; font-size: 10px; font-weight: 700; text-transform: uppercase; white-space: nowrap; color: var(--tab-color); border-bottom: 2px solid transparent; }
.order-tab:hover { color: var(--tab-color); background: #f6f8fb; text-decoration: none; }
.order-tab.active { border-bottom-color: var(--tab-color); background: #f2f7ff; }
.order-tab-count { font-size: 9px; line-height: 14px; padding: 0 4px; border: 1px solid currentColor; border-radius: 3px; }
.order-tab.active .order-tab-count { background: var(--tab-color); color: #fff; border-color: var(--tab-color); }
.order-tab-slate { --tab-color: #84909d; }.order-tab-cyan { --tab-color: #1399cf; }.order-tab-amber { --tab-color: #d99616; }.order-tab-green { --tab-color: #1a9c69; }.order-tab-orange { --tab-color: #dc8734; }.order-tab-red { --tab-color: #dc5d65; }
.order-list-meta { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 9px 17px; color: #8a95a3; font-size: 10px; background: #fff; border-bottom: 1px solid #edf0f4; }
.order-list-meta strong { color: #627289; font-weight: 500; }
.order-table-wrap { overflow: auto; flex: 1; min-height: 140px; background: white; margin: 0 10px; border: 1px solid #e3e7ed; border-top: 0; }
.order-table { border-collapse: separate; border-spacing: 0; table-layout: fixed; min-width: 1100px; width: 100%; }
.order-table th { position: sticky; top: 0; z-index: 2; background: #e9edf1; color: #647181; font-weight: 600; font-size: 10px; padding: 12px 9px; border-bottom: 1px solid #dae0e6; white-space: nowrap; }
.order-table td { padding: 12px 9px; border-bottom: 1px solid #edf0f3; vertical-align: middle; font-size: 11px; line-height: 1.6; overflow-wrap: anywhere; }
.order-table tbody tr:nth-child(even) { background: #f5f7f9; }
.order-table tbody tr:hover { background: #edf4ff; }
.order-table tbody tr.is-selected { background: #e7f0ff; }
.order-table a { color: #3d7cee; text-decoration: none; }.order-table a:hover { text-decoration: underline; }
.order-table input[type="checkbox"] { width: 13px; height: 13px; accent-color: #397df0; cursor: pointer; vertical-align: middle; }
.order-table .order-check-cell { width: 32px; text-align: center; padding-left: 10px; padding-right: 2px; }
.order-number { font-weight: 600; letter-spacing: .2px; }
.order-payment { display: inline-block; font-size: 8px; padding: 1px 4px; line-height: 1.5; text-transform: uppercase; border-radius: 2px; color: white; background: #8b99a9; margin-top: 4px; }
.order-payment-paid { background: #1ba669; }.order-payment-pending,.order-payment-initiated { background: #e9ae35; }.order-payment-failed,.order-payment-cancelled { background: #df7378; }.order-payment-refund_pending { background: #4a9fc5; }
.order-subtext { color: #8b97a6; font-size: 10px; display: block; }
.order-product { display: block; margin-bottom: 2px; }.order-product:last-child { margin-bottom: 0; }
.order-quantity { color: #8a96a5; white-space: nowrap; }
.order-money { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; font-weight: 600; }
.order-cod-due { color: #d78a19; }
.order-shipping { display: inline-flex; gap: 5px; align-items: baseline; font-size: 10px; }.order-shipping::before { content: ''; width: 5px; height: 5px; background: #a1afbf; border-radius: 50%; flex-shrink: 0; }
.order-shipping-delivered::before { background: #19a16e; }.order-shipping-delivering::before { background: #e0aa34; }.order-shipping-cancelled::before,.order-shipping-return::before,.order-shipping-returned::before { background: #dd6571; }
.order-footer { background: white; padding: 10px 17px; display: flex; gap: 12px; align-items: center; justify-content: space-between; border-top: 1px solid #e1e6ec; color: #8592a2; font-size: 10px; min-height: 53px; padding-right: 165px; }
.order-footer .pagination { margin: 0; }.order-footer .page-link { padding: 5px 9px; font-size: 11px; color: #59718d; }.order-footer .active .page-link { color: white; background: #397df0; border-color: #397df0; }
.order-empty { text-align: center; padding: 60px 20px !important; color: #8493a5; }.order-empty strong { display: block; font-size: 14px; color: #5d6d81; margin-bottom: 8px; }
.orders-screen .alert { margin: 10px 15px; font-size: 12px; }
@media (max-width: 1100px) { .order-toolbar { gap: 7px; }.order-filter-grid { grid-template-columns: repeat(2,minmax(130px,1fr)); }.order-search { max-width: none; }.order-heading { width: auto; }.order-toolbar > .order-control { max-width: 145px; } }
@media (max-width: 767px) { .order-toolbar { padding: 10px; }.order-search { flex-basis: 180px; }.order-tabs { padding: 0 6px; }.order-tab { padding: 12px 8px; }.order-filter-grid { gap: 8px; }.order-list-meta > span:last-child { display: none; }.order-footer { flex-wrap: wrap; padding: 9px 12px 48px; }.order-table-wrap { margin: 0; } }
</style>
@endpush

@section('content')
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
    <symbol id="order-search-icon" viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></symbol>
    <symbol id="order-download-icon" viewBox="0 0 24 24"><path d="M12 3v12m-4-4 4 4 4-4M4 16v5h16v-5"/></symbol>
    <symbol id="order-left-icon" viewBox="0 0 24 24"><path d="m14 7-5 5 5 5"/></symbol>
    <symbol id="order-right-icon" viewBox="0 0 24 24"><path d="m10 7 5 5-5 5"/></symbol>
    <symbol id="order-refresh-icon" viewBox="0 0 24 24"><path d="M20 11a8 8 0 1 0-2 7M20 4v7h-7"/></symbol>
</svg>
<div class="order-workspace">
    <form method="GET" action="{{ route('admin.orders.index') }}" id="order-filter-form">
        <input type="hidden" name="tab" value="{{ $activeTab }}">
        <div class="order-toolbar">
            <h1 class="order-heading">Đơn hàng</h1>
            <div class="order-pager">
                @if($orders->onFirstPage())
                    <button type="button" class="order-tool" disabled aria-label="Trang trước"><svg class="order-icon"><use href="#order-left-icon"/></svg></button>
                @else
                    <a href="{{ $orders->previousPageUrl() }}" class="order-tool" aria-label="Trang trước"><svg class="order-icon"><use href="#order-left-icon"/></svg></a>
                @endif
                <span>{{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }} / {{ number_format($orders->total()) }}</span>
                @if($orders->hasMorePages())
                    <a href="{{ $orders->nextPageUrl() }}" class="order-tool" aria-label="Trang sau"><svg class="order-icon"><use href="#order-right-icon"/></svg></a>
                @else
                    <button type="button" class="order-tool" disabled aria-label="Trang sau"><svg class="order-icon"><use href="#order-right-icon"/></svg></button>
                @endif
            </div>
            <select name="per_page" class="order-control order-auto-filter" aria-label="Số đơn mỗi trang">
                @foreach([25, 50, 100] as $size)<option value="{{ $size }}" @selected((int) ($filters['per_page'] ?? 25) === $size)>Hiển thị: {{ $size }}</option>@endforeach
            </select>
            <select name="gateway" class="order-control order-auto-filter" aria-label="Phương thức thanh toán">
                <option value="">Tất cả thanh toán</option>
                @foreach(['cod' => 'Thanh toán COD', 'momo' => 'Thanh toán MoMo', 'unknown' => 'Chưa xác định'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['gateway'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="order-search">
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100" aria-label="Tìm đơn hàng" placeholder="Mã đơn, khách hàng, SĐT, sản phẩm…">
                <button type="submit" aria-label="Tìm kiếm"><svg class="order-icon"><use href="#order-search-icon"/></svg></button>
            </div>
            <a href="{{ route('admin.orders.index', $filters) }}" class="order-tool" title="Tải lại danh sách" aria-label="Tải lại danh sách"><svg class="order-icon"><use href="#order-refresh-icon"/></svg></a>
            <button type="button" class="order-tool order-export" id="order-export" @disabled($orders->isEmpty())><svg class="order-icon"><use href="#order-download-icon"/></svg><span id="order-export-label">Xuất trang này</span></button>
        </div>
        <details class="order-filters" @if(collect($filters)->only(['date_from', 'date_to', 'payment_status', 'shipping_status', 'status'])->filter()->isNotEmpty()) open @endif>
            <summary>Bộ lọc nâng cao</summary>
            <div class="order-filter-grid">
                <div><label for="order-date-from">Từ ngày tạo đơn</label><input type="date" name="date_from" id="order-date-from" class="order-control" value="{{ $filters['date_from'] ?? '' }}"></div>
                <div><label for="order-date-to">Đến ngày tạo đơn</label><input type="date" name="date_to" id="order-date-to" class="order-control" value="{{ $filters['date_to'] ?? '' }}"></div>
                <div><label for="order-payment-status">Trạng thái thanh toán</label><select name="payment_status" id="order-payment-status" class="order-control"><option value="">Tất cả trạng thái</option>@foreach($paymentLabels as $value => $label)<option value="{{ $value }}" @selected(($filters['payment_status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
                <div><label for="order-sort">Sắp xếp</label><select name="sort" id="order-sort" class="order-control">@foreach(['newest' => 'Mới nhất trước', 'oldest' => 'Cũ nhất trước', 'amount_desc' => 'Tổng tiền giảm dần', 'amount_asc' => 'Tổng tiền tăng dần'] as $value => $label)<option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ $label }}</option>@endforeach</select></div>
                <div class="order-filter-actions"><button type="submit" class="order-tool order-apply">Áp dụng</button><a href="{{ route('admin.orders.index') }}" class="order-tool">Xóa lọc</a></div>
                @if(!empty($filters['shipping_status']) || !empty($filters['status']))
                    <div><label for="order-legacy-status">Trạng thái đơn hàng</label><select name="status" id="order-legacy-status" class="order-control"><option value="">Tất cả</option>@foreach(['pending' => 'Chờ thanh toán', 'paid' => 'Đã thanh toán', 'paid_momo' => 'Đã thanh toán MoMo', 'cod_ordered' => 'Đã đặt COD', 'cod_paid' => 'COD đã thu tiền', 'cancelled' => 'Đã hủy'] as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
                    <div><label for="order-shipping-status">Trạng thái giao hàng</label><select name="shipping_status" id="order-shipping-status" class="order-control"><option value="">Tất cả</option>@foreach($shippingLabels as $value => $label)<option value="{{ $value }}" @selected(($filters['shipping_status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
                @endif
            </div>
        </details>
    </form>
    @if(session('success'))<div class="alert alert-success" role="alert">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <nav class="order-tabs" aria-label="Trạng thái đơn hàng">
        @foreach($tabs as $key => $tab)
            <a href="{{ route('admin.orders.index', array_merge(collect($filters)->except(['page', 'tab', 'shipping_status'])->all(), ['tab' => $key])) }}" class="order-tab order-tab-{{ $tab['color'] }} {{ $activeTab === $key ? 'active' : '' }}" @if($activeTab === $key) aria-current="page" @endif>
                {{ $tab['label'] }}<span class="order-tab-count">{{ number_format($tab['count']) }}</span>
            </a>
        @endforeach
    </nav>
    <div class="order-list-meta"><span id="order-selection-count" aria-live="polite"><strong>{{ number_format($orders->total()) }} đơn hàng</strong> trong danh sách</span><span>Trạng thái vận chuyển được cập nhật từ GHN</span></div>
    <div class="order-table-wrap">
        <table class="order-table" id="order-table">
            <caption class="sr-only">Danh sách đơn hàng và trạng thái thanh toán, giao hàng</caption>
            <thead><tr>
                <th class="order-check-cell"><input type="checkbox" id="order-select-all" aria-label="Chọn tất cả đơn trên trang" @disabled($orders->isEmpty())></th>
                <th style="width: 120px" scope="col">Mã đơn hàng</th><th style="width: 125px" scope="col">Ngày tạo đơn</th><th style="width: 185px" scope="col">Sản phẩm</th>
                <th style="width: 98px" scope="col" class="text-right">Tổng tiền</th><th style="width: 92px" scope="col" class="text-right">COD cần thu</th>
                <th style="width: 140px" scope="col">Tên khách hàng</th><th style="width: 115px" scope="col">Mã vận đơn</th><th style="width: 155px" scope="col">Trạng thái giao hàng</th><th style="width: 70px" scope="col">Đơn vị VC</th>
            </tr></thead>
            <tbody>
                @forelse($orders as $order)
                    @php
                        $code = 'DH'.str_pad($order->id, 6, '0', STR_PAD_LEFT);
                        $paymentLabel = $paymentLabels[$order->payment_status] ?? $order->payment_status;
                        if ($order->gateway === 'cod' && $order->payment_status === 'pending') {
                            $paymentLabel = 'COD - chờ thu tiền';
                        }
                        $codDue = $order->gateway === 'cod' && in_array($order->payment_status, ['pending', 'failed'], true) && $order->status !== 'cancelled' && !in_array($order->shipping_status, ['cancelled', 'return', 'returning', 'returned', 'return_transporting', 'return_sorting'], true) ? $order->total_price : 0;
                        $shippingLabel = $shippingLabels[$order->shipping_status] ?? $order->shipping_status;
                        $productText = $order->items->map(fn ($item) => ($item->product->name ?? 'Sản phẩm đã xóa').' × '.$item->quantity)->implode('; ');
                    @endphp
                    <tr data-export="{{ json_encode([$code, $order->created_at->format('d/m/Y H:i'), $productText, $order->total_price, $codDue, $order->name, $order->phone, $order->ghn_order_code ?? '', $shippingLabel, $paymentLabel]) }}">
                        <td class="order-check-cell"><input type="checkbox" class="order-row-select" aria-label="Chọn đơn {{ $code }}"></td>
                        <td><a class="order-number" href="{{ route('admin.orders.show', $order->id) }}">{{ $code }}</a><br><span class="order-payment order-payment-{{ $order->payment_status }}">{{ $paymentLabel }}</span></td>
                        <td>{{ $order->created_at->format('d/m/Y') }}<span class="order-subtext">{{ $order->created_at->format('H:i') }}</span></td>
                        <td>
                            @forelse($order->items->take(2) as $item)
                                <span class="order-product">@if($item->product)<a href="{{ route('admin.products.show', $item->product_id) }}">{{ $item->product->name }}</a>@else<span>Sản phẩm đã xóa</span>@endif <span class="order-quantity">× {{ $item->quantity }}</span></span>
                            @empty<span class="order-subtext">Chưa có sản phẩm</span>@endforelse
                            @if($order->items->count() > 2)<a class="order-subtext" href="{{ route('admin.orders.show', $order->id) }}">+{{ $order->items->count() - 2 }} sản phẩm khác</a>@endif
                        </td>
                        <td class="order-money">{{ number_format($order->total_price, 0, ',', '.') }}<span class="order-subtext">đ</span></td>
                        <td class="order-money {{ $codDue > 0 ? 'order-cod-due' : '' }}">{{ number_format($codDue, 0, ',', '.') }}<span class="order-subtext">đ</span></td>
                        <td><span title="{{ $order->address }}">{{ $order->name }}</span><span class="order-subtext">{{ $order->phone }}</span></td>
                        <td>@if($order->ghn_order_code)<a href="{{ route('admin.orders.show', $order->id) }}">{{ $order->ghn_order_code }}</a>@else<span class="order-subtext">Chưa có vận đơn</span>@endif</td>
                        <td><span class="order-shipping order-shipping-{{ $order->shipping_status }}">{{ $shippingLabel }}</span></td>
                        <td>{{ $order->ghn_order_code ? 'GHN' : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="order-empty"><strong>Không tìm thấy đơn hàng</strong>Thử thay đổi từ khóa hoặc bộ lọc để xem kết quả.<br><a href="{{ route('admin.orders.index') }}">Xóa tất cả bộ lọc</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="order-footer"><span>Hiển thị {{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }} trong {{ number_format($orders->total()) }} đơn hàng</span>{{ $orders->links('pagination::bootstrap-4') }}</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('order-filter-form');
    document.querySelectorAll('.order-auto-filter').forEach(control => control.addEventListener('change', () => form.requestSubmit()));
    const checks = Array.from(document.querySelectorAll('.order-row-select'));
    const selectAll = document.getElementById('order-select-all');
    const selection = document.getElementById('order-selection-count');
    const initialSelection = selection.innerHTML;
    const exportLabel = document.getElementById('order-export-label');
    const updateSelection = () => {
        const count = checks.filter(check => check.checked).length;
        selectAll.checked = checks.length > 0 && count === checks.length;
        selectAll.indeterminate = count > 0 && count < checks.length;
        checks.forEach(check => check.closest('tr').classList.toggle('is-selected', check.checked));
        if (count) selection.textContent = 'Đã chọn ' + count + ' đơn trên trang này';
        else selection.innerHTML = initialSelection;
        exportLabel.textContent = count ? 'Xuất ' + count + ' đơn đã chọn' : 'Xuất trang này';
    };
    selectAll.addEventListener('change', () => { checks.forEach(check => { check.checked = selectAll.checked; }); updateSelection(); });
    checks.forEach(check => check.addEventListener('change', updateSelection));
    document.getElementById('order-export').addEventListener('click', () => {
        const selected = checks.filter(check => check.checked);
        const rows = (selected.length ? selected : checks).map(check => JSON.parse(check.closest('tr').dataset.export));
        if (!rows.length) return;
        const headers = ['Mã đơn', 'Ngày tạo', 'Sản phẩm', 'Tổng tiền', 'COD cần thu', 'Khách hàng', 'Số điện thoại', 'Mã vận đơn', 'Giao hàng', 'Thanh toán'];
        const csvCell = value => {
            let text = String(value ?? '');
            if (/^[=+@\-\t\r]/.test(text)) text = "'" + text;
            return '"' + text.replace(/"/g, '""') + '"';
        };
        const csv = '\uFEFF' + [headers, ...rows].map(row => row.map(csvCell).join(',')).join('\r\n');
        const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8;' }));
        const link = document.createElement('a');
        link.href = url;
        link.download = 'don-hang-' + new Date().toISOString().slice(0, 10) + '.csv';
        document.body.appendChild(link);
        link.click();
        link.remove();
        setTimeout(() => URL.revokeObjectURL(url), 1000);
    });

});
</script>
@endpush


