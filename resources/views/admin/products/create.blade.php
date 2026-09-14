@extends('layouts.admin')

@section('content')
<div class="container">
    <h2>➕ Thêm sản phẩm mới</h2>

    <form action="{{ route('admin.products.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name">Tên sản phẩm</label>
            <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
        </div>

        <div class="form-group">
            <label for="price">Giá (VNĐ)</label>
            <input type="number" name="price" step="1000" min="0" class="form-control" required value="{{ old('price') }}">
        </div>

        <div class="form-group">
            <label for="quantity">Số lượng</label>
            <input type="number" name="quantity" min="0" class="form-control" required value="{{ old('quantity') }}">
        </div>

        <div class="form-group">
            <label for="category_id">Danh mục</label>
            <select name="category_id" class="form-control" required>
                <option value="">-- Chọn danh mục --</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">💾 Lưu sản phẩm</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">⬅ Quay lại</a>
    </form>
</div>
@endsection
