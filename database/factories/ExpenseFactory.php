<?php

namespace Database\Factories;

use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'expense_category_id' => ExpenseCategory::factory(),
            'expense_date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'note' => fake()->optional()->sentence(),
            'user_id' => User::factory()->create()->id,
        ];
    }
}