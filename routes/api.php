<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('/users', App\Http\Controllers\Api\UserController::class);
Route::apiResource('/category', App\Http\Controllers\Api\CategoriController::class);
Route::apiResource('/products', App\Http\Controllers\Api\ProductController::class);
Route::apiResource('/carts', App\Http\Controllers\Api\CartController::class);
Route::apiResource('/carts-item', App\Http\Controllers\Api\CartItemController::class);
Route::apiResource('/likes', App\Http\Controllers\Api\LikeController::class);
Route::apiResource('/likes-item', App\Http\Controllers\Api\LikeItemController::class);
Route::apiResource('/rekening', App\Http\Controllers\Api\RekeningController::class);
Route::apiResource('/payment', App\Http\Controllers\Api\PaymentController::class);
Route::apiResource('/navbar', App\Http\Controllers\Api\NavbarController::class);
Route::apiResource('/section', App\Http\Controllers\Api\SectionController::class);
Route::apiResource('/content', App\Http\Controllers\Api\ContentController::class);
Route::apiResource('/review', App\Http\Controllers\Api\ReviewController::class);

// route untuk register, login dan logout
Route::prefix('account')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('/orders', App\Http\Controllers\Api\OrderController::class);
});