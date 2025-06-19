<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOtp extends Model
{
    use HasFactory;
    // fillable tells Laravel which fields are allowed to be mass-assigned (e.g., when you use CustomerOtp::create([...])). If a field is not in $fillable, Laravel will ignore it during create() or update() unless you force it.
    protected $fillable = [
        'email',
        'otp',
        'expires_at',
        'attempts',
    ];
}
