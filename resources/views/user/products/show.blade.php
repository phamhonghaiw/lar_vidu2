@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Chi tiết sản phẩm</h2>

    <div class="form-group">
        <strong>Tên:</strong> {{ $product->name }}
    </div>

    <div class="form-group">
        <strong>Danh mục:</strong> {{ $product->category->name ?? 'Không có' }}
    </div>

    <a href="{{ route('products.index') }}" class="btn btn-secondary">Quay lại</a>
</div>
@endsection
