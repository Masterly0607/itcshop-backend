<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Admin Routes
// Apply auth:sanctum and admin middleware
// auth:sanctum = checks if the user is authenticated using Laravel Sanctum.
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
   Route::get('/user', function (Request $request) {
      return $request->user(); // user() = Auth::user() : It returns the currently authenticated user making the request. If it is unauthenticated, it return null
   });
   Route::post('/logout', [AuthController::class, 'logout']);

   // Product CRUD
   Route::apiResource('/product', ProductController::class);

   // Restore soft deleted product by id
   Route::put('/product/{id}/restore', [ProductController::class, 'restore']);
});
Route::post('/login', [AuthController::class, 'login']);

// Customer routes