<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $isFlashSale = $this->faker->boolean(30); // 30% chance

        return [
            'title' => $this->faker->words(3, true),
            'slug' => $this->faker->slug,
            'image' => $this->faker->imageUrl(),
            'image_mime' => 'image/jpeg',
            'image_size' => rand(100, 1000),
            'description' => $this->faker->paragraph(4),
            'price' => $this->faker->randomFloat(2, 20, 5000),
            'is_flash_sale' => $isFlashSale,
            'flash_sale_start' => $isFlashSale ? now()->subDays(rand(0, 2)) : null,
            'flash_sale_end' => $isFlashSale ? now()->addDays(rand(1, 5)) : null,
            'is_best_selling' => $this->faker->boolean(20),
            'category_id' => Category::inRandomOrder()->first()?->id,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
