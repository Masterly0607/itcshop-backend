<?php

// app/Models/Customer.php
namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasApiTokens, Notifiable;

    // Add to $fillable which field that user can input or update):
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'address',
        'phone',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
    ]; // It hides the password field when returning the model as JSON. You don’t want to send passwords to the frontend or expose them in API data, even if they’re hashed.
    public function carts()
{
    return $this->hasMany(Cart::class);
}
}
