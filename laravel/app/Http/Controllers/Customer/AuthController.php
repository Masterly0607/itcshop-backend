<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Auth\LoginRequest;
use App\Http\Requests\Customer\Auth\RegisterRequest;
use App\Http\Resources\Customer\CustomerResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendCustomerOtpEmail;
use App\Models\Customer;
use App\Models\CustomerOtp;

class AuthController extends Controller
{
    // Register
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $customer = Customer::create([
            'first_name'  => $data['first_name'],
            'last_name'   => $data['last_name'],
            'email'       => $data['email'],
            'phone'       => $data['phone'] ?? null,
            'password'    => Hash::make($data['password']),
            'is_verified' => false,
        ]);

        // Rate limit OTP
        $recentOtps = CustomerOtp::where('email', $data['email'])
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        if ($recentOtps >= 3) {
            return response()->json(['message' => 'Too many OTP requests. Try again later.'], 429);
        }

        // Generate & save OTP
        $otp = rand(100000, 999999);
        CustomerOtp::create([
            'email' => $data['email'],
            'otp' => $otp,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Send OTP instantly (you can switch to job later)
      (new \App\Jobs\SendCustomerOtpEmail('sokmasterlychanon06@gmail.com', $otp))->handle();




        return response()->json([
            'message' => 'Registration successful. OTP sent to your email.',
        ]);
    }

    // Resend OTP
    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:customers,email',
        ]);

        $customer = Customer::where('email', $request->email)->first();
        if ($customer->is_verified) {
            return response()->json(['message' => 'Your account is already verified.'], 400);
        }

        // Rate limit resend
        $recentOtps = CustomerOtp::where('email', $request->email)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        if ($recentOtps >= 3) {
            return response()->json(['message' => 'Too many OTP requests. Try again later.'], 429);
        }

        // Generate new OTP
        $otp = rand(100000, 999999);
        CustomerOtp::create([
            'email' => $request->email,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Send with queue
    
// Register

(new \App\Jobs\SendCustomerOtpEmail($request->email, $otp))->handle();




        return response()->json([
            'message' => 'OTP resent to your email.',
        ]);
    }

    // Verify OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:customers,email',
            'otp' => 'required|numeric',
        ]);

        $otpRow = CustomerOtp::where('email', $request->email)
            ->where('otp', $request->otp)
            ->where('expires_at', '>=', now())
            ->first();

        if (!$otpRow) {
            $latest = CustomerOtp::where('email', $request->email)->latest()->first();
            if ($latest) {
                $latest->increment('attempts');
                if ($latest->attempts >= 5) {
                    return response()->json(['message' => 'Too many wrong attempts.'], 429);
                }
            }

            return response()->json(['message' => 'Invalid or expired OTP'], 400);
        }

        $customer = Customer::where('email', $request->email)->first();
        $customer->is_verified = true;
        $customer->save();

        CustomerOtp::where('email', $request->email)->delete();

        $token = $customer->createToken('customer-token')->plainTextToken;

        return response()->json([
            'message'  => 'Email verified and logged in successfully',
            'token'    => $token,
            'customer' => new CustomerResource($customer),
        ]);
    }

    // Login
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $customer = Customer::where('email', $credentials['email'])->first();

        if (!$customer || !Hash::check($credentials['password'], $customer->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$customer->is_verified) {
            return response()->json(['message' => 'Please verify your email before logging in.'], 403);
        }

        $token = $customer->createToken('customer-token')->plainTextToken;

        return response()->json([
            'token'    => $token,
            'customer' => new CustomerResource($customer),
        ]);
    }

    // Profile
    public function profile(Request $request)
    {
        return new CustomerResource($request->user());
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
