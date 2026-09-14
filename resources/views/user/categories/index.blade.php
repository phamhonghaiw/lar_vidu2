@extends('layouts.user')

@section('content')
<div class="container">
    <h2 class="mb-4">📂 Danh sách danh mục sản phẩm</h2>

    <div class="row">
        @forelse($categories as $category)
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm p-3">
                    <h5 class="text-primary">{{ $category->name }}</h5>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-muted">Không có danh mục nào.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
