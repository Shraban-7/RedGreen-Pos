<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::post('/', [CategoryController::class, 'store']);
    Route::get('/{slug}', [CategoryController::class, 'show']);
    Route::put('/{slug}', [CategoryController::class, 'update']);
    Route::delete('/{slug}', [CategoryController::class, 'destroy']);

});
