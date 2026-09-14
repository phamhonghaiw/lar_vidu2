@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">📦 Chi tiết sản phẩm</h2>

    <table class="table table-bordered">
        <tr>
            <th>ID</th>
            <td>{{ $product->id }}</td>
        </tr>
        <tr>
            <th>Tên sản phẩm</th>
            <td>{{ $product->name }}</td>
        </tr>
        <tr>
            <th>Giá</th>
            <td>{{ number_format($product->price, 0, ',', '.') }} VNĐ</td>
        </tr>
        <tr>
            <th>Số lượng</th>
            <td>{{ $product->quantity ?? '0' }}</td>
        </tr>
        <tr>
            <th>Danh mục</th>
            <td>{{ $product->category->name ?? 'Không có' }}</td>
        </tr>
        <tr>
            <th>Ngày tạo</th>
            <td>{{ $product->created_at }}</td>
        </tr>
    </table>

    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary mt-3">← Quay lại danh sách</a>
</div>
@endsection
