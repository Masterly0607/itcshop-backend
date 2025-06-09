<?php

use App\Http\Controllers\Admin\AdminAccountController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
  // Admin login (no auth required)
  Route::post('/login', [AuthController::class, 'login']);
  Route::post('/create-admin', [AdminAccountController::class, 'store']);

  // Protected admin routes
  Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
      return $request->user(); // Get logged-in admin
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // Product CRUD
    Route::apiResource('/products', ProductController::class);

    // Restore soft-deleted product
    Route::put('/product/{id}/restore', [ProductController::class, 'restore']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::apiResource('/categories', CategoryController::class);
    Route::apiResource('/users', UserController::class);
    Route::apiResource('/customers', CustomerController::class);
    Route::apiResource('/coupons', CouponController::class);
    Route::patch('/coupons/{id}', [CouponController::class, 'update']);

    Route::apiResource('/orders', OrderController::class)->only(['index', 'show']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
  });
});
