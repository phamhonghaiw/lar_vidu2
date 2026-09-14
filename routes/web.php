<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// ================= USER CONTROLLERS =================
use App\Http\Controllers\User\CartController as UserCartController;
use App\Http\Controllers\User\OrderController as OrderController;
use App\Http\Controllers\User\ChatController as UserChatController;
use App\Http\Controllers\User\WelcomeController as UserWelcomeController;
use App\Http\Controllers\User\GHNWebhookController as GHNWebhookController;
use App\Http\Controllers\User\GHNController;
use App\Http\Controllers\User\MomoController;

// ================= ADMIN CONTROLLERS =================
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ChatController as AdminChatController;
use App\Http\Controllers\Admin\ReportController;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;


/*
|--------------------------------------------------------------------------
| 🌐 ROUTES CHO KHÁCH (Guest)
|--------------------------------------------------------------------------
*/

Route::get('/', [UserWelcomeController::class, 'index'])->name('welcome');
Route::get('/product/{product}', [UserWelcomeController::class, 'detail'])->name('product.detail');
Route::get('/categories', [UserWelcomeController::class, 'categories'])->name('user.categories.index');

// Auth
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');


/*
|--------------------------------------------------------------------------
| 🚚 THIRD-PARTY WEBHOOKS & CALLBACKS (GHN, MOMO IPN)
|--------------------------------------------------------------------------
| NOTE: 
| - Không dùng middleware 'auth' vì bên thứ 3 (GHN, MoMo) gọi sang tự động.
| - Đã được bypass CSRF trong bootstrap/app.php.
|--------------------------------------------------------------------------
*/

Route::post('/ghn/webhook', [GHNWebhookController::class, 'handle'])->name('ghn.webhook');
Route::post('/payment/momo/ipn', [MomoController::class, 'ipn'])->name('payment.momo.ipn');
Route::get('/payment/momo/callback', [MomoController::class, 'callback'])->name('user.payment.momo.callback');


/*
|--------------------------------------------------------------------------
| 🧑‍💼 ROUTES CHO ADMIN
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/charts', [ReportController::class, 'charts'])->name('reports.charts');

    // CRUD
    Route::resource('products', AdminProductController::class);
    Route::resource('categories', AdminCategoryController::class);
    Route::resource('users', AdminUserController::class);
    Route::resource('orders', AdminOrderController::class)->except(['update']);

    // Chat Admin
    Route::get('/chat/users', [AdminChatController::class, 'getUsers'])->name('chat.users');
    Route::get('/chat/messages/{userId}', [AdminChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/send', [AdminChatController::class, 'send'])->name('chat.send');
});


/*
|--------------------------------------------------------------------------
| 👤 ROUTES CHO USER
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->prefix('user')->name('user.')->group(function () {

    // Products
    Route::get('/products', [UserWelcomeController::class, 'index'])->name('products.index');

    // Cart
    Route::get('/cart', [UserCartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{product}', [UserCartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update/{id}', [UserCartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove/{id}', [UserCartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/clear', [UserCartController::class, 'clear'])->name('cart.clear');

    // Payment
    Route::get('/payment', [OrderController::class, 'index'])->name('payment.index');
    Route::post('/payment/process', [OrderController::class, 'processPayment'])->name('payment.process');

    Route::get('/orders/{order}/pay/momo', [MomoController::class, 'payAgain'])->name('orders.momo.pay');
    Route::get('/orders/{order}/start-momo', [MomoController::class, 'start'])->name('orders.momo.start');

    // Orders
    Route::get('/orders', [OrderController::class, 'orderHistory'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    // Chat User
    Route::post('/chat/send', [UserChatController::class, 'send'])->name('chat.send');
    Route::get('/chat/messages', [UserChatController::class, 'getMessages'])->name('chat.messages');
});

Route::prefix('locations')->name('locations.')->group(function () {
    Route::get('/provinces', [GHNController::class, 'getProvinces'])->name('provinces');
    Route::get('/districts/{provinceId}', [GHNController::class, 'getDistricts'])->name('districts');
    Route::get('/wards/{districtId}', [GHNController::class, 'getWards'])->name('wards');
    Route::post('/calculate-fee', [GHNController::class, 'getShippingFee'])->name('fee');
});




/*
|--------------------------------------------------------------------------
| 📧 EMAIL VERIFICATION
|--------------------------------------------------------------------------
*/

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('welcome');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');