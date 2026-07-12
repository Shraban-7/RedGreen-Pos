<?php

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class)->in('Feature');

uses(RefreshDatabase::class);

test('POS - Sellable products endpoint returns products with stock', function () {
    $user = User::factory()->create();
    $productWithStock = Product::factory()->create([
        'stock_in' => 10,
        'stock_out' => 2
    ]);
    $productWithoutStock = Product::factory()->create([
        'stock_in' => 0,
        'stock_out' => 0
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/pos/products');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'data'
        ]);

    $responseData = $response->json('data');
    $productIds = collect($responseData)->pluck('id')->toArray();

    expect($productIds)->toContain($productWithStock->id);
    expect($productIds)->not->toContain($productWithoutStock->id);
});

test('POS - Create sale with valid data', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'stock_in' => 10,
        'stock_out' => 0,
        'selling_price' => 100
    ]);
    $customer = Customer::factory()->create();

    $saleData = [
        'customer_id' => $customer->id,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => 100,
            ]
        ],
        'payment_method' => 'cash',
        'paid_amount' => 200,
    ];

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/pos/sales', $saleData);

    $response->assertStatus(201)
        ->assertJson([
            'status' => true,
            'message' => 'Sale created successfully',
        ]);

    $this->assertDatabaseHas('sales', [
        'user_id' => $user->id,
        'customer_id' => $customer->id,
        'grand_total' => 200,
        'paid_amount' => 200,
        'due_amount' => 0,
        'status' => SaleStatus::COMPLETED->value,
    ]);

    $this->assertDatabaseHas('sale_items', [
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 100,
        'total' => 200,
    ]);

    $this->assertDatabaseHas('payments', [
        'method' => PaymentMethod::CASH->value,
        'amount' => 200,
    ]);

    // Check stock was updated
    $product->refresh();
    expect($product->stock_out)->toBe(2);
});

test('POS - Reject sale with insufficient stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'stock_in' => 1,
        'stock_out' => 0,
        'selling_price' => 100
    ]);

    $saleData = [
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 5, // More than available stock
                'unit_price' => 100,
            ]
        ],
        'payment_method' => 'cash',
        'paid_amount' => 500,
    ];

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/pos/sales', $saleData);

    $response->assertStatus(400)
        ->assertJson([
            'status' => false,
            'message' => 'Insufficient stock for ' . $product->name . '. Available: 1',
        ]);

    // Ensure no sale was created
    $this->assertDatabaseCount('sales', 0);
    $this->assertDatabaseCount('sale_items', 0);
    $this->assertDatabaseCount('payments', 0);

    // Ensure stock wasn't changed
    $product->refresh();
    expect($product->stock_out)->toBe(0);
});

test('POS - Void sale restores stock', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'stock_in' => 10,
        'stock_out' => 0,
        'selling_price' => 100
    ]);

    // Create a sale first
    $saleData = [
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 3,
                'unit_price' => 100,
            ]
        ],
        'payment_method' => 'cash',
        'paid_amount' => 300,
    ];

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/pos/sales', $saleData);

    $saleId = $response->json('data.id');

    // Verify initial state
    $product->refresh();
    expect($product->stock_out)->toBe(3);

    // Void the sale
    $voidResponse = $this->actingAs($user, 'sanctum')
        ->postJson("/api/pos/sales/{$saleId}/void");

    $voidResponse->assertStatus(200)
        ->assertJson([
            'status' => true,
            'message' => 'Sale voided successfully',
        ]);

    // Check that the sale status is voided
    $this->assertDatabaseHas('sales', [
        'id' => $saleId,
        'status' => SaleStatus::VOIDED->value,
    ]);

    // Check that stock was restored
    $product->refresh();
    expect($product->stock_out)->toBe(0);
});

test('POS - Partial refund updates stock and status', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'stock_in' => 10,
        'stock_out' => 0,
        'selling_price' => 100
    ]);

    // Create a sale with 2 items
    $saleData = [
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => 100,
            ]
        ],
        'payment_method' => 'cash',
        'paid_amount' => 200,
    ];

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/pos/sales', $saleData);

    $saleId = $response->json('data.id');
    $saleItemId = Sale::find($saleId)->items->first()->id;

    // Verify initial state
    $product->refresh();
    expect($product->stock_out)->toBe(2);

    // Refund 1 item
    $refundData = [
        'items' => [
            [
                'sale_item_id' => $saleItemId,
                'quantity' => 1,
            ]
        ]
    ];

    $refundResponse = $this->actingAs($user, 'sanctum')
        ->postJson("/api/pos/sales/{$saleId}/refund", $refundData);

    $refundResponse->assertStatus(200)
        ->assertJson([
            'status' => true,
            'message' => 'Sale refunded successfully',
        ]);

    // Check that the sale status is partial refund
    $this->assertDatabaseHas('sales', [
        'id' => $saleId,
        'status' => SaleStatus::PARTIAL_REFUND->value,
    ]);

    // Check that stock was partially restored and refunded quantity tracked
    $saleItem = \App\Models\SaleItem::find($saleItemId);
    expect($saleItem->refunded_quantity)->toBe(1);

    $product->refresh();
    expect($product->stock_out)->toBe(1); // Reduced from 2 to 1
});