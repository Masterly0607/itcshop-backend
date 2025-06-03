<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CouponFactory extends Factory
{
    public function definition(): array
    {
        $type = $this->faker->randomElement(['fixed', 'percent']);
        $value = $type === 'fixed' ? $this->faker->numberBetween(5, 50) : $this->faker->numberBetween(10, 30);

        return [
            'code' => strtoupper(Str::random(8)), // e.g. AZ9X3PWT
            'type' => $type,
            'value' => $value,
            'usage_limit' => $this->faker->randomElement([null, 50, 100]),
            'used' => 0,
            'start_date' => now(),
            'end_date' => now()->addDays($this->faker->numberBetween(7, 30)),
            'is_active' => true,
        ];
    }
}
