<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Profile\ChangePasswordRequest;
use App\Http\Requests\Customer\Profile\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($request->user());
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

        $user->password = Hash::make($data['new_password']);
        $user->save();

        return response()->json(['message' => 'Password changed successfully']);
    }
}
