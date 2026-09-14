@extends('layouts.admin')

@section('content')
<div class="container">
    <h2 class="mb-4">📦 Danh sách sản phẩm</h2>

    <a href="{{ route('admin.products.create') }}" class="btn btn-success mb-3">+ Thêm sản phẩm</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-hover">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Tên sản phẩm</th>
                <th>Danh mục</th>
                <th>Giá</th>
                <th>Số lượng</th> <!-- Thêm dòng này -->
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
        @forelse($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category->name ?? 'Không có' }}</td>
                <td>{{ number_format($product->price, 0, ',', '.') }} VNĐ</td>
                <td>{{ $product->quantity }}</td> <!-- Thêm dòng này -->
                <td>
                    <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-info btn-sm">👁 Xem</a>
                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-warning btn-sm">✏️ Sửa</a>

                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa?')">🗑 Xóa</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center text-muted">Không có sản phẩm nào.</td> <!-- cập nhật colspan từ 5 lên 6 -->
            </tr>
        @endforelse
        </tbody>

    </table>
</div>
@endsection
