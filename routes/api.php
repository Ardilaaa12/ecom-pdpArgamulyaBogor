<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\IsCustomer;
use App\Http\Middleware\IsAdmin;

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
Route::apiResource('/orderDetail', App\Http\Controllers\Api\OrderDetailController::class);
Route::apiResource('/rekening', App\Http\Controllers\Api\RekeningController::class);
Route::apiResource('/payment', App\Http\Controllers\Api\PaymentController::class);
Route::apiResource('/navbar', App\Http\Controllers\Api\NavbarController::class);
Route::apiResource('/content', App\Http\Controllers\Api\ContentController::class);
Route::apiResource('/review', App\Http\Controllers\Api\ReviewController::class);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify', [AuthController::class, 'verify'])->name('verify');
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware(['auth:sanctum', IsCustomer::class])->group(function () {
    Route::apiResource('/section', App\Http\Controllers\Api\SectionController::class);
});

Route::middleware(['auth:sanctum', IsAdmin::class])->group(function () {
    Route::apiResource('/shipping', App\Http\Controllers\Api\ShippingControllers::class);
});