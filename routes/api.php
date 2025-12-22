<?php

use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use Symfony\Component\Routing\Loader\Configurator\Routes;

Route::middleware("auth:sanctum")->group(function () {
    Route::get('/user', function (Request $request) {
        return apiResponse(new UserResource($request->user()));
    });
});

Route::post("/register", [AuthController::class, "register"]);
Route::post("/login", [AuthController::class, "login"]);

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
});

Route::prefix("products")->group(function () {
    Route::get("/", [ProductController::class, "index"]);
    Route::post("/", [ProductController::class, "store"]);
    Route::get("/{slug}", [ProductController::class, "show"]);
    Route::put("/{slug}", [ProductController::class, "update"]);
    Route::delete("/{slug}", [ProductController::class, "destroy"]);
});
