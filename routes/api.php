<?php

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\OrderController;
use Symfony\Component\Routing\Loader\Configurator\Routes;

Route::middleware("auth:sanctum")->group(function () {
    Route::get('/user', function (Request $request) {
        return apiResponse(new UserResource($request->user()));
    });

    Route::prefix("categories")->group(function () {
        Route::get("/", [CategoryController::class, "index"]);
        Route::post("/", [CategoryController::class, "store"]);
        Route::get("/{slug}", [CategoryController::class, "show"]);
        Route::put("/{slug}", [CategoryController::class, "update"]);
        Route::delete("/{slug}", [CategoryController::class, "destroy"]);
    });

    Route::prefix("brands")->group(function () {
        Route::get("/", [BrandController::class, "index"]);
        Route::post("/", [BrandController::class, "store"]);
        Route::get("/{slug}", [BrandController::class, "show"]);
        Route::put("/{slug}", [BrandController::class, "update"]);
        Route::delete("/{slug}", [BrandController::class, "destroy"]);
        Route::put("/{slug}/status", [BrandController::class, "status"]);
    });

    Route::prefix("products")->group(function () {
        Route::get("/", [ProductController::class, "index"]);
        Route::post("/", [ProductController::class, "store"]);
        Route::get("/{slug}", [ProductController::class, "show"]);
        Route::put("/{slug}", [ProductController::class, "update"]);
        Route::delete("/{slug}", [ProductController::class, "destroy"]);
    });

    Route::apiResource('suppliers', SupplierController::class);

    // POS routes
    Route::prefix("pos")->group(function () {
        Route::get('/products', [PosController::class, 'getSellableProducts']); // GET /api/pos/products
        Route::get('/sales', [PosController::class, 'index']); // GET /api/pos/sales
        Route::get('/sales/{id}', [PosController::class, 'show']); // GET /api/pos/sales/{id}
        Route::post('/sales', [PosController::class, 'store']); // POST /api/pos/sales
        Route::put('/sales/{id}', [PosController::class, 'update']); // PUT /api/pos/sales/{id}
        Route::post('/sales/{id}/void', [PosController::class, 'void']); // POST /api/pos/sales/{id}/void
        Route::post('/sales/{id}/refund', [PosController::class, 'refund']); // POST /api/pos/sales/{id}/refund
        Route::post('/sales/{id}/payments', [PosController::class, 'addPayment']); // POST /api/pos/sales/{id}/payments
    });

    // Expense routes
    Route::prefix("expense-categories")->group(function () {
        Route::get("/", [ExpenseCategoryController::class, "index"]);
        Route::post("/", [ExpenseCategoryController::class, "store"]);
        Route::get("/{id}", [ExpenseCategoryController::class, "show"]);
        Route::put("/{id}", [ExpenseCategoryController::class, "update"]);
        Route::delete("/{id}", [ExpenseCategoryController::class, "destroy"]);
    });

    Route::prefix("expenses")->group(function () {
        Route::get("/", [ExpenseController::class, "index"]);
        Route::post("/", [ExpenseController::class, "store"]);
        Route::get("/{id}", [ExpenseController::class, "show"]);
        Route::put("/{id}", [ExpenseController::class, "update"]);
        Route::delete("/{id}", [ExpenseController::class, "destroy"]);
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Orders
    Route::prefix("orders")->group(function () {
        Route::get("/", [OrderController::class, "index"]);
        Route::get("/{id}", [OrderController::class, "show"]);
    });

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/sales', [ReportController::class, 'sales']);
        Route::get('/customers', [ReportController::class, 'customers']);
        Route::get('/expenses', [ReportController::class, 'expenses']);
        Route::get('/overall', [ReportController::class, 'overall']);
    });
});

Route::post("/register", [AuthController::class, "register"]);
Route::post("/login", [AuthController::class, "login"]);


