@extends('layouts.admin')

@section('content')
<div class="container">
    <h2>✏️ Sửa danh mục: {{ $category->name }}</h2>

    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Tên danh mục</label>
            <input type="text" name="name" class="form-control" required value="{{ old('name', $category->name) }}">
        </div>

        <button type="submit" class="btn btn-success">💾 Cập nhật</button>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">⬅ Quay lại</a>
    </form>
</div>
@endsection
