<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Controllers Import
use Modules\Website\Http\Controllers\AuthController;
use Modules\Website\Http\Controllers\AccountController;
use Modules\Website\Http\Controllers\WebsiteController;
use Modules\Website\Http\Controllers\PostController;
use Modules\Website\Http\Controllers\ProductController;
use Modules\Website\Http\Controllers\CartController;
use Modules\Website\Http\Controllers\CheckoutController;

// Config
$websitePrefix = config('website.route_prefix', 'website');

/*
|--------------------------------------------------------------------------
| WEB ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['web'])->group(function () use ($websitePrefix) {

    // ====================================================
    // 1. AUTHENTICATION (Guest)
    // ====================================================
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::get('/register', [AuthController::class, 'register'])->name('register');


    // ====================================================
    // 2. AUTHENTICATION (Authenticated)
    // ====================================================
    // Logout nằm trong prefix config (theo logic cũ của bạn)
    Route::middleware(['auth'])->prefix($websitePrefix)->group(function(){
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });


    // ====================================================
    // 3. PUBLIC PAGES (Trang chủ, Blog, Sản phẩm)
    // ====================================================

    // Trang chủ
    Route::get('/', [WebsiteController::class, 'home'])->name('home');
    Route::get('/help', [WebsiteController::class, 'help'])->name('help');

    // Sản phẩm
    Route::get('/shop', [ProductController::class, 'index'])->name('product.list');
    Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.detail');

    // Blog
    Route::get('/blog', function (Request $request) {
        // Nếu có tham số ?category=abc trên URL thì truyền vào view/livewire
        return view('Website::pages.blog.index', [
            'categorySlug' => $request->query('category')
        ]);
    })->name('blog.index');

    Route::get('/blog/{slug}', [PostController::class, 'detail'])->name('blog.detail');


    // ====================================================
    // 4. SHOPPING (Giỏ hàng & Thanh toán)
    // ====================================================

    // Giỏ hàng
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

    // Thanh toán (Checkout)
    Route::prefix('checkout')->name('checkout.')->group(function() {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::get('/success', [CheckoutController::class, 'success'])->name('success');
        Route::get('/momo-callback', [CheckoutController::class, 'momoCallback'])->name('momo.callback');
    });


    // ====================================================
    // 5. ACCOUNT ROUTES (Yêu cầu đăng nhập)
    // ====================================================
    Route::middleware(['auth'])->prefix('account')->name('account.')->group(function () {

        // Dashboard
        Route::get('/', [AccountController::class, 'index'])->name('dashboard');

        // Hồ sơ & Affiliate
        Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
        Route::get('/affiliate', [AccountController::class, 'affiliate'])->name('affiliate');

        // Đơn hàng
        Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
        Route::get('/orders/{code}', [AccountController::class, 'orderDetail'])->name('orders.detail');

        // Route Wishlist
        Route::get('/wishlist', [\Modules\Website\Http\Controllers\AccountController::class, 'wishlist'])
        ->name('wishlist');
        });

});
