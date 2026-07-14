<?php

use App\Http\Controllers\API\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('throttle:api-read')->group(function () {
    Route::get('products/low-stock', [ProductController::class, 'lowStock']);
    Route::apiResource('products', ProductController::class)->only([
        'index',
        'show',
    ]);
});

Route::middleware('throttle:api-write')->group(function () {
    Route::post('products/{product}/stock', [ProductController::class, 'adjustStock']);
    Route::apiResource('products', ProductController::class)->only([
        'store',
        'update',
        'destroy',
    ]);
});
