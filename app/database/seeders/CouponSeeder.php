<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        // Generate 10 fake coupons
        Coupon::factory()->count(10)->create();

        // Create specific test coupon
        Coupon::create([
            'code' => 'SAVE10',
            'type' => 'fixed',
            'value' => 10,
            'usage_limit' => 100,
            'used' => 0,
            'start_date' => now(),
            'end_date' => now()->addDays(15),
            'is_active' => true,
        ]);
    }
}
