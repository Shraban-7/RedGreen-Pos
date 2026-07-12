<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'user_id' => User::factory()->create()->id,
            'name' => fake()->word(),
            'slug' => fake()->slug(),
            'description' => fake()->sentence(),
            'sku' => 'PRD-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6)),
            'buying_price' => fake()->randomFloat(2, 10, 100),
            'selling_price' => fake()->randomFloat(2, 20, 200),
            'discount_type' => null,
            'discount_value' => null,
            'discount_amount' => 0,
            'discounted_price' => null,
            'stock_in' => fake()->numberBetween(0, 100),
            'stock_out' => 0,
            'low_stock_quantity' => 5,
        ];
    }
}