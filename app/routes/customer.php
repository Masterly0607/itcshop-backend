<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\ProfileController;

// Example route group
Route::prefix('customer')
  ->middleware(['auth:sanctum', 'is_customer'])
  ->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/logout', [AuthController::class, 'logout']);
  });

// Public
Route::post('/customer/login', [AuthController::class, 'login']);
