<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('/users', App\Http\Controllers\Api\UserController::class);
Route::apiResource('/navbar', App\Http\Controllers\Api\NavbarController::class);
Route::apiResource('/section', App\Http\Controllers\Api\SectionController::class);
Route::apiResource('/category', App\Http\Controllers\Api\CategoriController::class);
Route::apiResource('/products', App\Http\Controllers\Api\ProductController::class);
Route::apiResource('/carts', App\Http\Controllers\Api\CartController::class);
Route::apiResource('/carts-item', App\Http\Controllers\Api\CartItemController::class);
Route::apiResource('/likes', App\Http\Controllers\Api\LikeController::class);
Route::apiResource('/likes-item', App\Http\Controllers\Api\LikeItemController::class);
Route::apiResource('/orders', App\Http\Controllers\Api\OrderController::class);
Route::apiResource('/rekening', App\Http\Controllers\Api\RekeningController::class);
