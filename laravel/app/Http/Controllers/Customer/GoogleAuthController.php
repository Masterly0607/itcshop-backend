<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\Customer;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        $url = Socialite::driver('google')
            ->stateless()
            ->redirectUrl(config('services.google.redirect'))
            ->redirect()
            ->getTargetUrl();

        return response()->json(['url' => $url]);
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')
            ->stateless()
            ->redirectUrl(config('services.google.redirect'))
            ->user();

        $fullName = $googleUser->getName();
        $nameParts = explode(' ', $fullName, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        $user = Customer::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            // ✅ Create new verified customer
            $user = Customer::create([
                'first_name'  => $firstName,
                'last_name'   => $lastName,
                'email'       => $googleUser->getEmail(),
                'password'    => bcrypt(Str::random(24)),
                'google_id'   => $googleUser->getId(),
                'is_verified' => 1,
                'phone'       => '',
                'address'     => '',
            ]);
        } else {
            // ✅ Always force update google_id and is_verified
            $user->forceFill([
                'google_id'   => $user->google_id ?? $googleUser->getId(),
                'is_verified' => 1,
            ])->save();
        }

        // ✅ Create token
        $token = $user->createToken('customer-token')->plainTextToken;

        // ✅ Redirect to frontend with token
        return redirect("https://itcshop-customer.netlify.app/auth/google-success?token=$token");
    }
}
