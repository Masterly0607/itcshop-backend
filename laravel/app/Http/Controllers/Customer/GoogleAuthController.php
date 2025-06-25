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
            ->redirectUrl(config('services.google.redirect')) // force correct redirect URI
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
    // Create new customer
    $user = Customer::create([
        'first_name'  => $firstName,
        'last_name'   => $lastName,
        'email'       => $googleUser->getEmail(),
        'password'    => bcrypt(Str::random(24)),
        'google_id'   => $googleUser->getId(),
        'is_verified' => true,
        'phone'       => '',
        'address'     => '',
    ]);
} else {
    // Update google_id if it's missing
    if (!$user->google_id) {
        $user->update([
            'google_id'   => $googleUser->getId(),
            'is_verified' => true,
        ]);
    }
}


        // ✅ FIXED: Store token to use in redirect
        $token = $user->createToken('customer-token')->plainTextToken;

        // ✅ Redirect to frontend with token
        return redirect("http://localhost:5173/auth/google-success?token=$token");
    }
}
