@extends('layouts.admin')

@section('content')
<div class="container">
    <h2>➕ Thêm danh mục mới</h2>

    <form action="{{ route('admin.categories.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Tên danh mục</label>
            <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
        </div>

        <button type="submit" class="btn btn-primary">💾 Lưu</button>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">⬅ Quay lại</a>
    </form>
</div>
@endsection
