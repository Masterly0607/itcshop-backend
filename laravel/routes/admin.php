<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
  // Admin login (no auth required)
  Route::post('/login', [AuthController::class, 'login']);

  // Protected admin routes
  Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/user', function (Request $request) {
      return $request->user(); // Get logged-in admin
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // Product CRUD
    Route::apiResource('/product', ProductController::class);

    // Restore soft-deleted product
    Route::put('/product/{id}/restore', [ProductController::class, 'restore']);
  });
});
