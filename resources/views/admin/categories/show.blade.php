@extends('layouts.admin')

@section('content')
<div class="container">
    <h2>👁 Chi tiết danh mục</h2>

    <table class="table table-bordered">
        <tr>
            <th>ID</th>
            <td>{{ $category->id }}</td>
        </tr>
        <tr>
            <th>Tên danh mục</th>
            <td>{{ $category->name }}</td>
        </tr>
        <tr>
            <th>Ngày tạo</th>
            <td>{{ $category->created_at }}</td>
        </tr>
    </table>

    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">⬅ Quay lại</a>
</div>
@endsection
