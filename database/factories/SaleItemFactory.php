<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SaleItem>
 */
class SaleItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sale_id' => Sale::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 10),
            'unit_price' => fake()->randomFloat(2, 5, 100),
            'subtotal' => fake()->randomFloat(2, 10, 500),
            'discount_type' => null,
            'discount_value' => null,
            'discount_amount' => 0,
            'vat_percentage' => 0,
            'vat_amount' => fake()->randomFloat(2, 0, 50),
            'total' => fake()->randomFloat(2, 10, 500),
            'refunded_quantity' => 0,
        ];
    }
}
