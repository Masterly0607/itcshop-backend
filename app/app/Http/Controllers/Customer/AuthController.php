<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Auth\LoginRequest;
use App\Http\Requests\Customer\Auth\RegisterRequest;
use App\Http\Resources\Customer\CustomerResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\Customer;
use App\Models\CustomerOtp;

class AuthController extends Controller
{
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

        // ✅ Resend OTP protection
        $recentOtps = CustomerOtp::where('email', $data['email'])
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        if ($recentOtps >= 3) {
            return response()->json(['message' => 'Too many OTP requests. Try again later.'], 429);
        }

        // ✅ Generate and store OTP
        $otp = rand(100000, 999999);
        CustomerOtp::create([
            'email' => $data['email'],
            'otp' => $otp,
            'expires_at' => now()->addMinutes(5),
        ]);

        // ✅ Send via Gmail SMTP (raw email)
        Mail::raw("Your OTP code is: $otp", function ($message) use ($data) {
            $message->to($data['email'])->subject('Your OTP Code');
        });

        return response()->json([
            'message' => 'Registration successful. OTP sent to your email.',
        ]);
    }

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

        // ✅ Verified
        $customer = Customer::where('email', $request->email)->first();
        $customer->is_verified = true;
        $customer->save();

        // ✅ Clean up
        CustomerOtp::where('email', $request->email)->delete();

        // ✅ Auto login
        $token = $customer->createToken('customer-token')->plainTextToken;

        return response()->json([
            'message'  => 'Email verified and logged in successfully',
            'token'    => $token,
            'customer' => new CustomerResource($customer),
        ]);
    }

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

    public function profile(Request $request)
    {
        return new CustomerResource($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
