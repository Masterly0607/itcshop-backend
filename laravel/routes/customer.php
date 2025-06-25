<?php

use App\Http\Controllers\Admin\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\{
    AuthController,
    CartController,
    CheckoutController,
    CouponController,
    ForgotPasswordController,
    GoogleAuthController,
    OrderController,
    ProductViewController,
    ProfileController,
    StripePaymentController,
    PaymentMethodController,
    WishlistController
};

Route::prefix('customer')->group(function () {
    // Public Auth
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/login', [AuthController::class, 'login']);

    // Forgot Password
    Route::post('/forgot-password/send-otp', [ForgotPasswordController::class, 'sendOtp']);
    Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
    Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword']);

    // Google Auth
    Route::get('/auth/redirect/google', [GoogleAuthController::class, 'redirect']);
    Route::get('/auth/callback/google', [GoogleAuthController::class, 'callback']);

    // Public Product View
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductViewController::class, 'index']);
        Route::get('/flash-sale', [ProductViewController::class, 'flashSale']);
        Route::get('/best-selling', [ProductViewController::class, 'bestSelling']);
        Route::get('/new', [ProductViewController::class, 'newProducts']);
       Route::get('/{id}', [ProductViewController::class, 'show']); 
    });

    // Authenticated Routes
    Route::middleware('auth:sanctum')->group(function () {
        //  Auth
        Route::post('/logout', [AuthController::class, 'logout']);

        //  Profile
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::put('/change-password', [ProfileController::class, 'changePassword']);

        // Cart
        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart', [CartController::class, 'store']);
        Route::put('/cart/{id}', [CartController::class, 'update']);
        Route::delete('/cart/{id}', [CartController::class, 'destroy']);

        //  Wishlist
        Route::get('/wishlist', [WishlistController::class, 'index']);
        Route::post('/wishlist', [WishlistController::class, 'store']);
        Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy']);

        //  Coupon
        Route::post('/cart/apply-coupon', [CouponController::class, 'apply']);

        // Checkout
        Route::post('/checkout', [CheckoutController::class, 'store']);

        // Orders
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
  Route::get('/categories', [CategoryController::class, 'index']);
        //  Stripe Saved Cards (Wallet)
        Route::prefix('cards')->controller(PaymentMethodController::class)->group(function () {
            Route::get('/', 'index');       // GET customer/cards
            Route::post('/', 'addCard');    // POST customer/cards
            Route::delete('/{id}', 'destroy'); // DELETE customer/cards/{id}
        });

        // Stripe Payment
        Route::post('/stripe/payment-intent', [StripePaymentController::class, 'createPaymentIntent']); // for one-time
        Route::post('/stripe/charge', [StripePaymentController::class, 'payWithSavedCard']); // saved card
    });
});
