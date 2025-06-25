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
        // Resend OTP protection
        $recentOtps = CustomerOtp::where('email', $data['email']) // Filter row with email column =  $data['email'] inside customer_otp table using CustomerOTP model.
            ->where('created_at', '>=', now()->subMinutes(5)) // now()->subMinutes(5) = Time from 5 minutes ago
            ->count(); // Just count how many rows match the 2 conditons above, if not return 0.

        if ($recentOtps >= 3) {
            return response()->json(['message' => 'Too many OTP requests. Try again later.'], 429);
        }

        // Generate and store OTP
        $otp = rand(100000, 999999); // Generate a random number between 100000 and 999999 with always gives a 6-digit number.(111111 and 999999 are 6 digits number)
        CustomerOtp::create([ // Creates a new row in the customer_otps table using the CustomerOtp model
            'email' => $data['email'],
            'otp' => $otp,
            'expires_at' => now()->addMinutes(5), // Time the OTP will expire (5 mins from now)
        ]);

        // Send via Gmail SMTP
        Mail::raw("Your OTP code is: $otp", function ($message) use ($data) { // Mail = Laravel’s email system class, raw() = Sends plain text email (no view), use($data) = It's a way to bring a variable from outside into an inner function, $message = Email message object from Laravel that use to handle like: who to send to → to(...), what subject → subject(...)
            $message->to($data['email'])->subject('Your OTP Code');
        });

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

        // Check if user is already verified
        $customer = Customer::where('email', $request->email)->first();
        if ($customer->is_verified) {
            return response()->json(['message' => 'Your account is already verified.'], 400);
        }

        // Resend OTP protection
        $recentOtps = CustomerOtp::where('email', $request->email)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        if ($recentOtps >= 3) {
            return response()->json(['message' => 'Too many OTP requests. Try again later.'], 429);
        }

        // Generate and store new OTP
        $otp = rand(100000, 999999);
        CustomerOtp::create([
            'email' => $request->email,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Send OTP email
      SendCustomerOtpEmail::dispatch($data['email'], $otp);


        return response()->json([
            'message' => 'OTP resent to your email.',
        ]);
    }

    // Verify OTP function
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:customers,email',
            'otp' => 'required|numeric',
        ]);

        //  Find OTP record that is valid
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

        // Mark as verified
        $customer = Customer::where('email', $request->email)->first();
        $customer->is_verified = true;
        $customer->save(); // Save all updated fields to the DB

        // Cleanup
        CustomerOtp::where('email', $request->email)->delete();

        // Auto login
        $token = $customer->createToken('customer-token')->plainTextToken;

        return response()->json([
            'message'  => 'Email verified and logged in successfully',
            'token'    => $token,
            'customer' => new CustomerResource($customer),
        ]);
    }

    // Login function
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        $customer = Customer::where('email', $credentials['email'])->first();

        if (!$customer || !Hash::check($credentials['password'], $customer->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        } // Hash::check(...) = Laravel helper to check if the password matches the hashed password from DB

        if (!$customer->is_verified) {
            return response()->json(['message' => 'Please verify your email before logging in.'], 403);
        }

        $token = $customer->createToken('customer-token')->plainTextToken;

        return response()->json([
            'token'    => $token,
            'customer' => new CustomerResource($customer),
        ]);
    }

    // Get user information function
    public function profile(Request $request)
    {
        return new CustomerResource($request->user());
    }

    // Logout function
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
