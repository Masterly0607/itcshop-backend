<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Auth\LoginRequest;
use App\Http\Requests\Customer\Auth\RegisterRequest;



use App\Http\Resources\Customer\CustomerResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Customer;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();


        $customer = Customer::create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'] ?? null,
            'password'   => Hash::make($data['password']),
        ]);

        $token = $customer->createToken('customer-token')->plainTextToken;

        return response()->json([
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
