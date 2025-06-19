<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    /** @use HasFactory<\Database\Factories\CouponFactory> */
    use HasFactory;
    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'discount_value' => 'float',
        'min_order_amount' => 'float',
    ];
}
