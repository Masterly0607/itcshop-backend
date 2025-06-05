<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
  // Login 
  public function login(Request $request)
  {
    $credentials = $request->validate([
      'email' => ['required', 'email'],
      'password' => 'required',
      'remember' => 'boolean'
    ]);
    $remember = $credentials['remember'] ?? false; // if checks if the left side is null(have key, no value) or not set(no key). If no, it go to the right
    unset($credentials['remember']);

    // Check if the email or password is matched in the database!
    // Auth::attempt = Laravel’s built-in authentication system. Auth::attempt check: Does a user with that email exist in the database?, Does the provided password match the stored hashed password?
    if (!Auth::attempt($credentials, $remember)) {
      return response([
        'message' => 'Email or password is incorrect.'
      ], 422);
    }

    //  Check if the user is admin!
    // Auth::user() = It returns the currently authenticated user object. Ex: {"id": 1,  "name": "Sok Masterly",  "email": "sok@example.com",}
    $user = Auth::user();
    if (!$user->is_admin) {
      Auth::logout();
      return response([
        'message' => "You don't have permission to authenicate as admin."
      ]);
    }

    //  If everthing is fine, create new token for user
    $token = $user->createToken('main')->plainTextToken;
    return response([
      'user' =>  new UserResource($user),
      'token' => $token,
    ]);
  }

  // Logout
  public function logout()
  {
    $user = Auth::user();
    $user->currentAccessToken()->delete();
    return response([
      'message' => "You are logged out."
    ]);
  }
}
