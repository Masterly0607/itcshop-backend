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
        'type',
        'value',
        'usage_limit',
        'used',
        'start_date',
        'end_date',
        'is_active',
    ];
}
