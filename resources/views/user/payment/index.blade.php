@extends('layouts.user')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Thanh toán đơn hàng</h2>

    <form id="checkoutForm" method="POST" action="{{ route('user.payment.process') }}">
        @csrf

        <div class="row">
            <!-- Thông tin khách hàng & Địa chỉ giao hàng -->
            <div class="col-md-7">
                <div class="card p-3 shadow-sm mb-4">
                    <h5 class="mb-3">📍 Thông tin giao hàng</h5>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ auth()->user()->name ?? '' }}" required>
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="phone" class="form-control" value="{{ auth()->user()->phone ?? '' }}" required>
                        </div>

                        <!-- 3 cấp địa chỉ GHN -->
                        <div class="col-md-4">
                            <label for="province_select" class="form-label">Tỉnh / Thành <span class="text-danger">*</span></label>
                            <select id="province_select" class="form-select" required>
                                <option value="">-- Chọn Tỉnh/Thành --</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="district_select" class="form-label">Quận / Huyện <span class="text-danger">*</span></label>
                            <select id="district_select" name="to_district_id" class="form-select" required disabled>
                                <option value="">-- Chọn Quận/Huyện --</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="ward_select" class="form-label">Phường / Xã <span class="text-danger">*</span></label>
                            <select id="ward_select" name="to_ward_code" class="form-select" required disabled>
                                <option value="">-- Chọn Phường/Xã --</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label for="address" class="form-label">Địa chỉ chi tiết (Số nhà, tên đường) <span class="text-danger">*</span></label>
                            <input type="text" name="address" id="address" class="form-control" placeholder="Ví dụ: Số 20, Ngõ 15..." required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Giỏ hàng & Tóm tắt chi phí -->
            <div class="col-md-5">
                <div class="card p-3 shadow-sm mb-4">
                    <h5>🛒 Chi tiết giỏ hàng</h5>
                    <ul class="list-group list-group-flush my-3">
                        @php $total = 0; @endphp
                        @foreach($cart as $item)
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <div>
                                    <strong>{{ $item['name'] }}</strong>
                                    <div class="text-muted small">Số lượng: x{{ $item['quantity'] }}</div>
                                </div>
                                <div>{{ number_format($item['price'] * $item['quantity']) }} VNĐ</div>
                                @php $total += $item['price'] * $item['quantity']; @endphp
                            </li>
                        @endforeach
                    </ul>

                    <hr>

                    <div class="d-flex justify-content-between my-1">
                        <span>Tiền hàng:</span>
                        <strong id="subtotal_text">{{ number_format($total) }} VNĐ</strong>
                    </div>

                    <div class="d-flex justify-content-between my-1">
                        <span>Phí vận chuyển (GHN):</span>
                        <strong id="shipping_fee_text" class="text-primary">0 VNĐ</strong>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between my-2">
                        <h5>Tổng thanh toán:</h5>
                        <h5 id="final_total_text" class="text-danger fw-bold">{{ number_format($total) }} VNĐ</h5>
                    </div>

                    <input type="hidden" name="total_price" id="total_price_input" value="{{ $total }}">

                    <!-- Nút thanh toán -->
                    <div class="mt-3 d-grid gap-2">
                        <button type="submit" class="btn btn-success py-2" name="payment_method" value="momo">
                            📱 Thanh toán qua Ví MoMo
                        </button>

                        <button type="submit" class="btn btn-secondary py-2" name="payment_method" value="cod">
                            💵 Thanh toán khi nhận hàng (COD)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const provinceSelect = document.getElementById('province_select');
    const districtSelect = document.getElementById('district_select');
    const wardSelect = document.getElementById('ward_select');
    const shippingFeeText = document.getElementById('shipping_fee_text');
    const finalTotalText = document.getElementById('final_total_text');
    const totalPriceInput = document.getElementById('total_price_input');
    const districtsUrl = "{{ route('locations.districts', ['provinceId' => '__PROVINCE__']) }}";
    const wardsUrl = "{{ route('locations.wards', ['districtId' => '__DISTRICT__']) }}";

    // Lấy tiền hàng an toàn từ input ẩn
    const subtotal = parseInt(totalPriceInput ? totalPriceInput.value : 0) || 0;

    // 1. Tải danh sách Tỉnh/Thành phố từ GHN
    fetch("{{ route('locations.provinces') }}")
        .then(res => res.json())
        .then(res => {
            if (res.data) {
                let options = '<option value="">-- Chọn Tỉnh/Thành --</option>';
                res.data.forEach(p => {
                    options += `<option value="${p.ProvinceID}">${p.ProvinceName}</option>`;
                });
                provinceSelect.innerHTML = options;
            } else {
                provinceSelect.innerHTML = '<option value="">-- Không tải được tỉnh/thành --</option>';
            }
        })
        .catch(err => {
            console.error("Lỗi load tỉnh thành:", err);
            provinceSelect.innerHTML = '<option value="">-- Lỗi kết nối GHN --</option>';
        });

    // 2. Khi chọn Tỉnh -> Tải Quận/Huyện
    provinceSelect.addEventListener('change', function () {
        districtSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
        districtSelect.disabled = true;
        wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
        wardSelect.disabled = true;
        updateTotals(0);

        if (!this.value) return;

        fetch(districtsUrl.replace('__PROVINCE__', this.value))
            .then(res => res.json())
            .then(res => {
                if (res.data) {
                    let options = '<option value="">-- Chọn Quận/Huyện --</option>';
                    res.data.forEach(d => {
                        options += `<option value="${d.DistrictID}">${d.DistrictName}</option>`;
                    });
                    districtSelect.innerHTML = options;
                    districtSelect.disabled = false;
                } else {
                    districtSelect.innerHTML = '<option value="">-- Không tải được quận/huyện --</option>';
                }
            })
            .catch(err => {
                console.error("Lỗi load quận huyện:", err);
                districtSelect.innerHTML = '<option value="">-- Lỗi kết nối GHN --</option>';
            });
    });

    // 3. Khi chọn Quận/Huyện -> Tải Phường/Xã
    districtSelect.addEventListener('change', function () {
        wardSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
        wardSelect.disabled = true;
        updateTotals(0);

        if (!this.value) return;

        fetch(wardsUrl.replace('__DISTRICT__', this.value))
            .then(res => res.json())
            .then(res => {
                if (res.data) {
                    let options = '<option value="">-- Chọn Phường/Xã --</option>';
                    res.data.forEach(w => {
                        options += `<option value="${w.WardCode}">${w.WardName}</option>`;
                    });
                    wardSelect.innerHTML = options;
                    wardSelect.disabled = false;
                } else {
                    wardSelect.innerHTML = '<option value="">-- Không tải được phường/xã --</option>';
                }
            })
            .catch(err => {
                console.error("Lỗi load phường xã:", err);
                wardSelect.innerHTML = '<option value="">-- Lỗi kết nối GHN --</option>';
            });
    });

    // 4. Khi chọn Phường/Xã -> Tính cước vận chuyển GHN
    wardSelect.addEventListener('change', function () {
        if (!this.value || !districtSelect.value) return;

        shippingFeeText.innerText = 'Đang tính cước...';

        fetch("{{ route('locations.fee') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                to_district_id: districtSelect.value,
                to_ward_code: this.value
            })
        })
        .then(res => res.json())
        .then(res => {
            if (res.code === 200 && res.data) {
                const fee = parseInt(res.data.total) || 0;
                updateTotals(fee);
            } else {
                shippingFeeText.innerText = 'Chưa hỗ trợ';
                updateTotals(0);
            }
        })
        .catch(err => {
            console.error("Lỗi tính phí:", err);
            shippingFeeText.innerText = 'Lỗi tính phí';
            updateTotals(0);
        });
    });

    function updateTotals(fee) {
        shippingFeeText.innerText = new Intl.NumberFormat('vi-VN').format(fee) + ' VNĐ';
        const finalAmount = subtotal + fee;
        finalTotalText.innerText = new Intl.NumberFormat('vi-VN').format(finalAmount) + ' VNĐ';
        if (totalPriceInput) {
            totalPriceInput.value = finalAmount;
        }
    }
});
</script>
@endsection