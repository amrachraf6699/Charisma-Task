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

Route::post('products/{product}/stock', [ProductController::class, 'adjustStock']);
Route::get('products/low-stock', [ProductController::class, 'lowStock']);

Route::apiResource('products', ProductController::class);