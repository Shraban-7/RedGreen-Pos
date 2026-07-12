<?php

use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class)->in('Feature');

uses(RefreshDatabase::class);

test('Report - sales report returns aggregates', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    $customer = Customer::factory()->create();

    $sale = Sale::factory()->create([
        'grand_total' => 500,
        'subtotal' => 500,
        'discount_amount' => 0,
        'vat_amount' => 0,
        'customer_id' => $customer->id,
        'created_at' => now(),
    ]);

    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'total' => 500,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/reports/sales?from=' . now()->startOfMonth()->toDateString() . '&to=' . now()->endOfMonth()->toDateString());

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'gross_total',
                'sale_count',
                'daily_breakdown',
                'top_products',
                'payment_methods',
            ],
        ]);

    expect((float) $response->json('data.gross_total'))->toBe(500.0);
});

test('Report - expenses report returns total and breakdown', function () {
    $user = User::factory()->create();
    $category = ExpenseCategory::factory()->create();

    Expense::factory()->create([
        'amount' => 300,
        'expense_category_id' => $category->id,
        'expense_date' => now()->format('Y-m-d'),
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/reports/expenses?from=' . now()->startOfMonth()->toDateString() . '&to=' . now()->endOfMonth()->toDateString());

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => ['total', 'by_category'],
        ]);

    expect((float) $response->json('data.total'))->toBe(300.0);
});

test('Report - overall report returns net profit', function () {
    $user = User::factory()->create();
    $category = ExpenseCategory::factory()->create();

    Sale::factory()->create([
        'grand_total' => 1000,
        'created_at' => now(),
    ]);

    Expense::factory()->create([
        'amount' => 200,
        'expense_category_id' => $category->id,
        'expense_date' => now()->format('Y-m-d'),
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/reports/overall?from=' . now()->startOfMonth()->toDateString() . '&to=' . now()->endOfMonth()->toDateString());

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => ['total_sales', 'total_expenses', 'net_profit', 'trend'],
        ]);

    expect((float) $response->json('data.net_profit'))->toBe(800.0);
});

test('Report - customers report returns per-customer breakdown', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    Sale::factory()->create([
        'grand_total' => 750,
        'customer_id' => $customer->id,
        'created_at' => now(),
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/reports/customers?from=' . now()->startOfMonth()->toDateString() . '&to=' . now()->endOfMonth()->toDateString());

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => ['customers'],
        ]);

    expect($response->json('data.customers'))->toHaveCount(1);
});

test('Report - requires authentication', function () {
    $response = $this->getJson('/api/reports/overall');

    $response->assertStatus(401);
});