<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Jobs\SendCustomerOtpEmail;
use App\Models\Customer;
use App\Models\CustomerOtp;

class ForgotPasswordController extends Controller
{
    // 1. Send OTP to reset password
    public function sendOtp(Request $request)
    {
        $data =  $request->validate([
            'email' => 'required|email|exists:customers,email',
        ]);

        // Rate limit
        $recentOtps = CustomerOtp::where('email', $request->email)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        if ($recentOtps >= 3) {
            return response()->json(['message' => 'Too many OTP requests. Try again later.'], 429);
        }

        // Generate OTP
        $otp = rand(100000, 999999);
        CustomerOtp::create([
            'email'      => $request->email,
            'otp'        => $otp,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Send OTP (via job)
      (new \App\Jobs\SendCustomerOtpEmail($data['email'], $otp))->handle();

        return response()->json(['message' => 'OTP sent to your email.']);
    }

    // 2. Verify OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:customers,email',
            'otp'   => 'required|numeric',
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

        return response()->json(['message' => 'OTP verified. You can now reset your password.']);
    }

    // 3. Reset Password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|exists:customers,email',
            'otp'      => 'required|numeric',
            'password' => 'required|confirmed|min:6',
        ]);

        $otpRow = CustomerOtp::where('email', $request->email)
            ->where('otp', $request->otp)
            ->where('expires_at', '>=', now())
            ->first();

        if (!$otpRow) {
            return response()->json(['message' => 'Invalid or expired OTP'], 400);
        }

        $customer = Customer::where('email', $request->email)->first();
        $customer->password = Hash::make($request->password);
        $customer->save();

        CustomerOtp::where('email', $request->email)->delete();

        return response()->json(['message' => 'Password has been reset successfully.']);
    }
}
