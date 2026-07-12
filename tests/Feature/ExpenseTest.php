<?php

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class)->in('Feature');

uses(RefreshDatabase::class);

test('Expense - list returns paginated expenses', function () {
    $user = User::factory()->create();
    Expense::factory()->count(3)->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/expenses');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [['id', 'title', 'amount', 'expense_date']],
        ]);

    expect($response->json('data'))->toHaveCount(3);
});

test('Expense - create with valid data', function () {
    $user = User::factory()->create();
    $category = ExpenseCategory::factory()->create();

    $payload = [
        'title' => 'Office Rent',
        'amount' => 5000,
        'expense_category_id' => $category->id,
        'expense_date' => now()->format('Y-m-d'),
        'note' => 'Monthly rent',
    ];

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/expenses', $payload);

    $response->assertStatus(201)
        ->assertJson([
            'status' => true,
            'message' => 'Expense created successfully',
        ]);

    $this->assertDatabaseHas('expenses', [
        'title' => 'Office Rent',
        'amount' => 5000,
        'user_id' => $user->id,
    ]);
});

test('Expense - create rejects future date', function () {
    $user = User::factory()->create();
    $category = ExpenseCategory::factory()->create();

    $payload = [
        'title' => 'Future Expense',
        'amount' => 100,
        'expense_category_id' => $category->id,
        'expense_date' => now()->addDays(5)->format('Y-m-d'),
    ];

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/expenses', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['expense_date']);

    $this->assertDatabaseCount('expenses', 0);
});

test('Expense - show returns a single expense', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/expenses/{$expense->id}");

    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'data' => ['id' => $expense->id],
        ]);
});

test('Expense - update modifies the expense', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->create(['amount' => 100]);

    $response = $this->actingAs($user, 'sanctum')
        ->putJson("/api/expenses/{$expense->id}", [
            'title' => 'Updated Title',
            'amount' => 250,
            'expense_date' => now()->format('Y-m-d'),
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'message' => 'Expense updated successfully',
        ]);

    $this->assertDatabaseHas('expenses', [
        'id' => $expense->id,
        'title' => 'Updated Title',
        'amount' => 250,
    ]);
});

test('Expense - delete removes the expense', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/expenses/{$expense->id}");

    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'message' => 'Expense deleted successfully',
        ]);

    $this->assertDatabaseCount('expenses', 0);
});

test('Expense - requires authentication', function () {
    $response = $this->getJson('/api/expenses');

    $response->assertStatus(401);
});

test('ExpenseCategory - create and list', function () {
    $user = User::factory()->create();

    $create = $this->actingAs($user, 'sanctum')
        ->postJson('/api/expense-categories', [
            'name' => 'Utilities',
            'description' => 'Electricity, water',
        ]);

    $create->assertStatus(201)
        ->assertJson(['status' => true]);

    $list = $this->actingAs($user, 'sanctum')
        ->getJson('/api/expense-categories');

    $list->assertStatus(200)
        ->assertJsonStructure(['status', 'message', 'data' => [['id', 'name', 'slug']]]);
});