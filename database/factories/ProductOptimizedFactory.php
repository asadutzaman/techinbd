<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductOptimized>
 */
class ProductOptimizedFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'short_description' => fake()->sentence(),
            'base_price' => fake()->randomFloat(2, 10, 2000),
            'currency' => 'BDT',
            'stock_status' => 'in_stock',
            'total_stock' => 10,
            'status' => 1,
            'featured' => false,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => ['featured' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 0]);
    }
}
