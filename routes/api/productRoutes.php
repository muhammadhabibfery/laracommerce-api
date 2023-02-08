<?php

use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProductImageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Product API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Product API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('merchant')
    ->middleware(['auth:sanctum', 'verified', 'authRole:MERCHANT'])
    ->group(function () {
        Route::apiResource("products", ProductController::class);
        Route::apiResource("product-images", ProductImageController::class)
            ->except(['index', 'show', 'update']);
        Route::get("product-images/{product}", [ProductImageController::class, "index"])->name("product-images.index");
    });
