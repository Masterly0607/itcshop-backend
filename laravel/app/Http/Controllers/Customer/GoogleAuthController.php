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

        $user = Customer::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'password' => bcrypt(Str::random(24)),
                'google_id' => $googleUser->getId(),
            ]
        );

        $token = $user->createToken('auth_token')->plainTextToken;

        // return JSON response (Option 1 standard)
        return redirect("http://localhost:5173/auth/google-success?token=$token");
    }
}
