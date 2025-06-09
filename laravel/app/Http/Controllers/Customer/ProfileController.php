<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Profile\ChangePasswordRequest;
use App\Http\Requests\Customer\Profile\UpdateProfileRequest;
use App\Http\Resources\Customer\CustomerResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return new CustomerResource($request->user());
    }

    public function update(UpdateProfileRequest $request)
    {
        $data = $request->validated();


        $request->user()->update($data);

        return response()->json(['message' => 'Profile updated', 'customer' => $request->user()]);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 400);
        }

        // Update password
        $user->password = Hash::make($data['new_password']);
        $user->save();

        // ✅ Revoke all old tokens (logout everywhere)
        $user->tokens()->delete();

        // ✅ Create new token to auto-login (optional)
        $newToken = $user->createToken('customer-token')->plainTextToken;

        return response()->json([
            'message' => 'Password changed successfully',
            'token' => $newToken,
        ]);
    }
}
