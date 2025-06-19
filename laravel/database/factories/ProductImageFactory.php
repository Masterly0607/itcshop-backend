<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'image' => 'products/' . Str::uuid() . '.jpg', // ✅ required
            'image_mime' => 'image/jpeg',
            'image_size' => $this->faker->numberBetween(100, 5000),
        ];
    }
}
