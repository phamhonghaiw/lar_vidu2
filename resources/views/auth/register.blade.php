@extends('layouts.user')

@section('content')
<h2>Đăng ký tài khoản</h2>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('register') }}" method="POST">
    @csrf

    <div class="form-group">
        <label for="name">Họ và tên</label>
        <input type="text" name="name" id="name" class="form-control" required value="{{ old('name') }}">
    </div>

    <div class="form-group">
        <label for="email">Địa chỉ Email</label>
        <input type="email" name="email" id="email" class="form-control" required value="{{ old('email') }}">
    </div>

    <div class="form-group">
        <label for="password">Mật khẩu</label>
        <input type="password" name="password" id="password" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="password_confirmation">Xác nhận mật khẩu</label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-success">Đăng ký</button>
</form>
@endsection
