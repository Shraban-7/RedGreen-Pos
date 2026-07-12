<?php

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class)->in('Feature');

uses(RefreshDatabase::class);

test('Dashboard - returns aggregated summary', function () {
    $user = User::factory()->create();

    $category = ExpenseCategory::factory()->create();
    Expense::factory()->create([
        'amount' => 500,
        'expense_date' => now()->format('Y-m-d'),
        'expense_category_id' => $category->id,
    ]);

    $product = Product::factory()->create([
        'stock_in' => 2,
        'stock_out' => 0,
        'low_stock_quantity' => 5,
    ]);

    Sale::factory()->create([
        'grand_total' => 1000,
        'created_at' => now(),
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/dashboard');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'todaySales' => ['count', 'total'],
                'todayExpenses',
                'monthSales' => ['count', 'total'],
                'monthExpenses',
                'lowStockProducts',
                'recentSales',
                'salesChart' => ['labels', 'data'],
            ],
        ]);

    expect((float) $response->json('data.todaySales.total'))->toBe(1000.0);
    expect((float) $response->json('data.todayExpenses'))->toBe(500.0);
});

test('Dashboard - requires authentication', function () {
    $response = $this->getJson('/api/dashboard');

    $response->assertStatus(401);
});