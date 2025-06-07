<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\CouponController;
use App\Http\Controllers\Customer\ForgotPasswordController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\ProductViewController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\StripePaymentController;
use App\Http\Controllers\Customer\PaymentMethodController;
use App\Http\Controllers\Customer\WishlistController;

Route::prefix('customer')->group(function () {
  // Public auth
  Route::post('/register', [AuthController::class, 'register']);
  Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
  Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
  Route::post('/login', [AuthController::class, 'login']);

  // Forgot password
  Route::post('/forgot-password/send-otp', [ForgotPasswordController::class, 'sendOtp']);
  Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
  Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword']);

  // Public product viewing
  Route::prefix('products')->group(function () {
    Route::get('/', [ProductViewController::class, 'index']);
    Route::get('/flash-sale', [ProductViewController::class, 'flashSale']);
    Route::get('/best-selling', [ProductViewController::class, 'bestSelling']);
    Route::get('/new', [ProductViewController::class, 'newProducts']);
    Route::get('/{categoryName}', [ProductViewController::class, 'byCategoryName']);
  });

  // Authenticated routes
  Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile
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

    // Coupons
    Route::post('/cart/apply-coupon', [CouponController::class, 'apply']);

    // Checkout
    Route::post('/checkout', [CheckoutController::class, 'store']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);

    // Stripe: Saved Cards (Wallet)
    Route::prefix('cards')->controller(PaymentMethodController::class)->group(function () {
      Route::get('/', 'index');
      Route::post('/', 'addCard');
      Route::delete('/{id}', 'destroy');
    });


    // 🔹 For Stripe.js flow (one-time card entry)
    Route::post('/stripe/payment-intent', [StripePaymentController::class, 'createPaymentIntent']);

    // 🔹 For saved card payment
    Route::post('/stripe/charge', [StripePaymentController::class, 'payWithSavedCard']);
  });
});
