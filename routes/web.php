<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StripePaymentController;
use App\Models\Product;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard', [
        'products' => Product::all()
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/payment/intent', [StripePaymentController::class, 'createIntent'])->name('payment.intent');
    
    Route::get('/checkout/cart', function () {
        return Inertia::render('Stripe/Cart');
        })->name('checkout.cart');
        
    Route::get('/checkout/success', [StripePaymentController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/{id}', [StripePaymentController::class, 'show'])->name('checkout');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::post('/cart/sync', [OrderController::class, 'syncCart'])->name('cart.sync');

    Route::post('/checkout/process', [StripePaymentController::class, 'checkout'])->name('checkout.process');

    Route::post('/cart/coupon', [StripePaymentController::class, 'applyCoupon'])->name('cart.coupon');
    Route::get('/admin/coupons', [CouponController::class, 'index'])->name('coupons.index');
    Route::post('/admin/coupons', [CouponController::class, 'store'])->name('coupons.store');
    Route::delete('/admin/coupons/{coupon}', [CouponController::class, 'destroy'])->name('coupons.destroy');
    Route::patch('/admin/coupons/{coupon}/toggle', [CouponController::class, 'toggle'])->name('coupons.toggle');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
