<?php

use App\Http\Controllers\Api\Admin\OrderStatusController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CartCouponController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product:slug}', [ProductController::class, 'show']);

Route::get('/categories/{category}/products', [CategoryController::class, 'products']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
});
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/checkout', [CheckoutController::class, 'place']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::get('/cart', [CartController::class, 'show']);
    Route::post('/cart/items', [CartController::class, 'addItem']);
    Route::put('/cart/items/{item}', [CartController::class, 'updateQuantity']);
    Route::delete('/cart/items/{item}', [CartController::class, 'removeItem']);

    Route::post('/cart/coupon', [CartCouponController::class, 'apply']);
    Route::delete('/cart/coupon', [CartCouponController::class, 'remove']);
});

Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::post('/orders/{order}/ship', [OrderStatusController::class, 'markShipped']);
    Route::post('/orders/{order}/deliver', [OrderStatusController::class, 'markDelivered']);
    Route::post('/orders/{order}/cancel', [OrderStatusController::class, 'cancel']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/products/{product}/reviews', [ReviewController::class, 'storeForProduct']);
    Route::post('/vendors/{vendor}/reviews', [ReviewController::class, 'storeForVendor']);

    Route::get('/products/{product}/reviews', [ReviewController::class, 'indexForProduct']);
    Route::get('/vendors/{vendor}/reviews', [ReviewController::class, 'indexForVendor']);

    Route::get('/me/reviews', [ReviewController::class, 'mine']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);
});
