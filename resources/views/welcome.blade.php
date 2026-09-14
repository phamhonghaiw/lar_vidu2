@extends('layouts.user')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4 text-center">🛍️ Sản phẩm mới nhất</h2>

    @if(session('success'))
        <div class="alert alert-success text-center">
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        @forelse ($products as $product)
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="card h-100 shadow-sm border-0 rounded-4">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold text-primary">{{ $product->name }}</h5>

                        <p class="mb-1 text-muted">📁 Danh mục: 
                            <strong>{{ $product->category->name ?? 'Chưa phân loại' }}</strong>
                        </p>

                        <p class="mb-1">💰 Giá: 
                            <span class="text-danger fw-semibold">{{ number_format($product->price ?? 0) }} VNĐ</span>
                        </p>

                        <p class="mb-3">📦 Số lượng: 
                            <span class="fw-semibold">{{ $product->quantity }}</span>
                        </p>

                        <div class="mt-auto">
                            <a href="{{ route('product.detail', $product->id) }}" 
                               class="btn btn-outline-primary btn-sm w-100 mb-2 rounded-pill">
                                🔍 Xem chi tiết
                            </a>

                            <form action="{{ route('user.cart.add', $product->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm w-100 rounded-pill">
                                    🛒 Thêm vào giỏ
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center">
                <p class="text-muted fs-5">Hiện tại chưa có sản phẩm nào.</p>
            </div>
        @endforelse
    </div>

    {{-- Phân trang --}}
@if ($products->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $products->links('pagination::bootstrap-4') }}
    </div>
@endif



</div>
@endsection
