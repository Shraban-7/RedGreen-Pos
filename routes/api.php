<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use Symfony\Component\Routing\Loader\Configurator\Routes;

Route::middleware("auth:sanctum")->group(function () {});

Route::post("/register", [AuthController::class, "register"]);

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
