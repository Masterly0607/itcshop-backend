<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CouponController;
use App\Http\Controllers\Customer\ForgotPasswordController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\WishlistController;

Route::prefix('customer')->group(function () {
  Route::post('/register', [AuthController::class, 'register']);
  Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
  Route::post('/login', [AuthController::class, 'login']);

  // Forgot Password
  Route::post('/forgot-password/send-otp', [ForgotPasswordController::class, 'sendOtp']);
  Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
  Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword']);

  Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    // Profile Controller
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/change-password', [ProfileController::class, 'changePassword']);

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy']);


    Route::post('/cart/apply-coupon', [CouponController::class, 'apply']);
  });
});
