<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(Authenticate::using('sanctum'));

Route::apiResource('/rekening', App\Http\Controllers\Api\RekeningController::class);
Route::apiResource('/navbar', App\Http\Controllers\Api\NavbarController::class);
Route::apiResource('/section', App\Http\Controllers\Api\SectionController::class);
Route::apiResource('/content', App\Http\Controllers\Api\ContentController::class);
