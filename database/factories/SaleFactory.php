<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sale_code' => 'SALE-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6)),
            'user_id' => User::factory(),
            'customer_id' => null,
            'subtotal' => fake()->randomFloat(2, 10, 1000),
            'discount_type' => null,
            'discount_value' => null,
            'discount_amount' => 0,
            'vat_percentage' => 0,
            'vat_amount' => 0,
            'grand_total' => fake()->randomFloat(2, 10, 1000),
            'paid_amount' => fake()->randomFloat(2, 0, 1000),
            'due_amount' => fake()->randomFloat(2, 0, 1000),
            'status' => \App\Enums\SaleStatus::COMPLETED,
        ];
    }
}
