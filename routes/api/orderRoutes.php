<?php

use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\OrderCustomerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Order API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Order API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('merchant')
    ->middleware(['auth:sanctum', 'verified', 'authRole:MERCHANT'])
    ->group(function () {
        Route::apiResource("orders", OrderController::class)
            ->except(['store', 'update', 'destroy']);
    });

Route::middleware(["auth:sanctum", "verified", "authRole:CUSTOMER,MERCHANT,STAFF,ADMIN"])
    ->group(function () {
        Route::apiResource("orders", OrderCustomerController::class)
            ->names(['index' => 'orders.customer.index', 'show' => 'orders.customer.show'])
            ->only(['index', 'show']);
    });
