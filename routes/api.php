<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// middleware
use App\Http\Middleware\IsCustomer;
use App\Http\Middleware\IsAdmin;

// controllers
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoriController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CartItemController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\LikeItemController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderDetailController;
use App\Http\Controllers\Api\RekeningController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\NavbarController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ShippingControllers;

// route function index, store, show, update, destroy
Route::apiResource('/users', App\Http\Controllers\Api\UserController::class);
Route::apiResource('/category', App\Http\Controllers\Api\CategoriController::class);
Route::apiResource('/products', App\Http\Controllers\Api\ProductController::class);
Route::apiResource('/carts', App\Http\Controllers\Api\CartController::class);
Route::apiResource('/carts-item', App\Http\Controllers\Api\CartItemController::class);
Route::apiResource('/likes', App\Http\Controllers\Api\LikeController::class);
Route::apiResource('/likes-item', App\Http\Controllers\Api\LikeItemController::class);
Route::apiResource('/orders', App\Http\Controllers\Api\OrderController::class);
Route::apiResource('/orderDetail', App\Http\Controllers\Api\OrderDetailController::class);
Route::apiResource('/rekening', App\Http\Controllers\Api\RekeningController::class);
Route::apiResource('/payment', App\Http\Controllers\Api\PaymentController::class);
Route::apiResource('/navbar', App\Http\Controllers\Api\NavbarController::class);
Route::apiResource('/content', App\Http\Controllers\Api\ContentController::class);
Route::apiResource('/review', App\Http\Controllers\Api\ReviewController::class);

// route CRUD public function lain
Route::middleware('auth:sanctum')->get('/detail', [UserController::class, 'getUser']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify', [AuthController::class, 'verify'])->name('verify');
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// route yang sudah memiliki middleware
Route::middleware(['auth:sanctum', IsCustomer::class])->group(function () {
    Route::apiResource('/section', App\Http\Controllers\Api\SectionController::class);
});

Route::middleware(['auth:sanctum', IsAdmin::class])->group(function () {
    Route::apiResource('/shipping', App\Http\Controllers\Api\ShippingControllers::class);
});