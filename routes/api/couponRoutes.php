<?php

use App\Http\Controllers\API\CouponController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Coupon API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Coupon API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('merchant')
    ->middleware(['auth:sanctum', 'verified', 'authRole:MERCHANT'])
    ->group(function () {
        Route::apiResource("coupons", CouponController::class)
            ->except(['show', 'update']);
    });
