@extends('layouts.admin')

@section('content')
<div class="container">
    <h2 class="mb-4">📁 Danh sách danh mục</h2>

    <a href="{{ route('admin.categories.create') }}" class="btn btn-success mb-3">+ Thêm danh mục</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên danh mục</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
        @forelse($categories as $category)
            <tr>
                <td>{{ $category->id }}</td>
                <td>{{ $category->name }}</td>
                <td>
                    <a href="{{ route('admin.categories.show', $category->id) }}" class="btn btn-info btn-sm">👁Xem</a>
                    <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-primary btn-sm">Sửa</a>
                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('Xóa danh mục này?')">Xóa</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="3">Chưa có danh mục nào.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
