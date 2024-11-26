<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// middleware
use App\Http\Middleware\IsCustomer;
use App\Http\Middleware\IsAdmin;

// route function index, store, show, update, destroy
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoriController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderDetailController;
use App\Http\Controllers\Api\RekeningController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\NavbarController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ShippingControllers;

// fitur search
Route::get('/product/search', [ProductController::class, 'search']);
Route::get('/pages/search', [NavbarController::class, 'search']);
Route::get('/section/search', [SectionController::class, 'search']);
Route::get('/content/search', [ContentController::class, 'search']);
Route::get('/category/search', [CategoriController::class, 'search']);
Route::get('/order/search', [OrderController::class, 'search']);
Route::get('/payment/search', [PaymentController::class, 'search']);
Route::get('/shipping/search', [ShippingControllers::class, 'search']);
Route::get('/rekening/search', [RekeningController::class, 'search']);
Route::get('/user/search', [UserController::class, 'search']);

// route function index, store, show, update, destroy
Route::apiResource('/users', App\Http\Controllers\Api\UserController::class);
Route::apiResource('/category', App\Http\Controllers\Api\CategoriController::class);
Route::apiResource('/products', App\Http\Controllers\Api\ProductController::class);
Route::apiResource('/orders', App\Http\Controllers\Api\OrderController::class);
Route::apiResource('/orderDetail', App\Http\Controllers\Api\OrderDetailController::class);
Route::apiResource('/rekening', App\Http\Controllers\Api\RekeningController::class);
Route::apiResource('/payment', App\Http\Controllers\Api\PaymentController::class);
Route::apiResource('/navbar', App\Http\Controllers\Api\NavbarController::class);
Route::apiResource('/section', App\Http\Controllers\Api\SectionController::class);
Route::apiResource('/content', App\Http\Controllers\Api\ContentController::class);
Route::apiResource('/shipping', App\Http\Controllers\Api\ShippingControllers::class);


// login-logout-register
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify', [AuthController::class, 'verify']);
Route::post('/login', [AuthController::class, 'login']);

// perhitungan data penjualan
Route::get('/penjualan', [OrderController::class, 'monthlyData']);

// laporan penjualan
Route::get('/order/status', [OrderController::class, 'status']);
Route::get('/order/statusBerhasil', [OrderController::class, 'statusBerhasil']);
Route::get('/order/statusGagal', [OrderController::class, 'statusGagal']);

// ubah status order
Route::put('/order/statusBerhasil/{id}', [OrderDetailController::class, 'updateStatusBerhasil']);
Route::put('/order/statusGagal/{id}', [OrderDetailController::class, 'updateStatusGagal']);

// ubah status pengiriman / shipping
Route::put('/shipping/statusKirim/{shippingId}', [ShippingControllers::class, 'updateStatusPengiriman']);
Route::put('/shipping/statusSampai/{shippingId}', [ShippingControllers::class, 'updateStatusSampai']);

// Route export pdf (struk pembayaran)
Route::get('/invoice/download/{orderId}', [PaymentController::class, 'generateInvoice']);

// route yang sudah memiliki middleware
Route::middleware(['auth:sanctum'])->group(function () {
    // menghitung biaya cart yang diceklis
    Route::post('/cart/total/{itemId}', [CartController::class, 'updateStatus']);

    // data pengguna login saja
    Route::get('/detail', [UserController::class, 'getUser']);

    // membuat pesanan
    Route::post('/order/checkout', [OrderDetailController::class, 'store']);

    // data order pengguna login saja
    Route::get('/order/see', [OrderDetailController::class, 'see']);
    
    //logout
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('/review', App\Http\Controllers\Api\ReviewController::class);
    Route::apiResource('/likes', App\Http\Controllers\Api\LikeController::class);
    Route::apiResource('/carts', App\Http\Controllers\Api\CartController::class);
});

Route::middleware(['auth:sanctum', IsCustomer::class])->group(function () {
});

Route::middleware(['auth:sanctum', IsAdmin::class])->group(function () {
});