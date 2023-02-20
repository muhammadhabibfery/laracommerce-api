<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CheckoutController;

/*
|--------------------------------------------------------------------------
| Checkout API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Checkout API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post("checkout/payment/notification", [CheckoutController::class, "notificationHandler"])->name("checkout.payment-notification");

Route::middleware(['auth:sanctum', 'verified', 'authRole:CUSTOMER,MERCHANT,STAFF,ADMIN'])
    ->group(function () {
        Route::post("checkout/shipping", [CheckoutController::class, "shipping"])->name("checkout.shipping");
        Route::post("checkout/get-courier-services", [CheckoutController::class, "process"])->name("checkout.process");
        Route::post("checkout/coupon/validate", [CheckoutController::class, "couponValidate"])->name("checkout.coupon-validate");
        Route::post("checkout/payment", [CheckoutController::class, "submit"])->name("checkout.submit");
    });
