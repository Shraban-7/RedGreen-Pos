<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'payment_code' => 'PAY-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6)),
            'sale_id' => Sale::factory(),
            'user_id' => User::factory(),
            'amount' => fake()->randomFloat(2, 10, 500),
            'method' => fake()->randomElement(['cash', 'card', 'mobile_banking', 'bank_transfer']),
            'type' => 'payment',
        ];
    }
}
