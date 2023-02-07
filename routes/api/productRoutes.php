<?php

use App\Http\Controllers\API\ProductController;
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

Route::middleware(['auth:sanctum', 'verified', 'authRole:MERCHANT'])
    ->prefix('merchant')
    ->group(function () {
        Route::apiResource("products", ProductController::class);
    });
