@extends('layouts.admin')

@section('content')
<div class="container">
    <h2>✏️ Sửa sản phẩm: {{ $product->name }}</h2>

    <form action="{{ route('admin.products.update', $product->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Tên sản phẩm</label>
            <input type="text" name="name" class="form-control" required
                   value="{{ old('name', $product->name) }}">
        </div>

        <div class="form-group">
            <label for="price">Giá (VNĐ)</label>
            <input type="number" name="price" step="1000" min="0" class="form-control" required
                   value="{{ old('price', $product->price) }}">
        </div>

        <div class="form-group">
            <label for="quantity">Số lượng</label>
            <input type="number" name="quantity" min="0" class="form-control" required
                   value="{{ old('quantity', $product->quantity) }}">
        </div>

        <div class="form-group">
            <label for="category_id">Danh mục</label>
            <select name="category_id" class="form-control" required>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}"
                        {{ $product->category_id == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-success">💾 Cập nhật</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">⬅ Quay lại</a>
    </form>
</div>
@endsection
