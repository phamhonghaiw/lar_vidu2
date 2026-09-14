<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    // Hiển thị form đăng ký
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    // Xử lý đăng ký
public function register(Request $request)
{
    $data = $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users,email',
        'password' => 'required|confirmed|min:6',
    ]);

    $user = User::create([
        'name'     => $data['name'],
        'email'    => $data['email'],
        'password' => bcrypt($data['password']),
        'role'     => 'user', // Mặc định là user
    ]);

    // Gửi email xác thực
    $user->sendEmailVerificationNotification();

    Auth::login($user);

    return redirect()->route('verification.notice')
                     ->with('success', 'Vui lòng kiểm tra email để xác thực tài khoản.');
}


    // Hiển thị form đăng nhập
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Xử lý đăng nhập
    public function login(Request $request)
    {
        // Bước 1: Validate dữ liệu đầu vào
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Bước 2: Kiểm tra đăng nhập
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user(); // lấy người dùng hiện tại

            // Bước 3: Kiểm tra quyền
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard')
                                ->with('success', 'Chào mừng quản trị viên!');
            } else {
                return redirect()->route('welcome')
                                ->with('success', 'Đăng nhập thành công!');
            }
        }

        // Nếu đăng nhập thất bại
        return back()->with('error', 'Email hoặc mật khẩu không đúng');
    }

    // Đăng xuất
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Đã đăng xuất thành công');
    }
}
