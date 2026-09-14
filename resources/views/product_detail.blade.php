@extends('layouts.user')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">🔍 Chi tiết sản phẩm</h2>

    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="card-title">{{ $product->name }}</h4>

            @if ($product->category)
                <p class="text-muted">📁 Danh mục: {{ $product->category->name }}</p>
            @endif

            <p>💰 Giá: {{ number_format($product->price ?? 0, 0, ',', '.') }} VNĐ</p>
            <p>📦 Số lượng: {{ $product->quantity ?? 0 }}</p>
            <p>📝 Mô tả: {{ $product->description ?? 'Không có mô tả' }}</p>

            {{-- Nút thêm vào giỏ nếu còn hàng --}}
            @if($product->quantity > 0)
                <form action="{{ route('user.cart.add', $product->id) }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-success">🛒 Thêm vào giỏ hàng</button>
                </form>
            @else
                <p class="text-danger mt-3">⚠️ Sản phẩm đã hết hàng</p>
            @endif
        </div>
    </div>

    <a href="{{ route('welcome') }}" class="btn btn-secondary mt-4">⬅ Quay lại danh sách</a>
</div>
@endsection
