<?php

use App\Http\Controllers\API\MerchantAccountController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Merchant Account API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Merchant Account API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('merchant')
    ->middleware(['auth:sanctum', 'verified'])
    ->group(function () {
        Route::post("accounts", [MerchantAccountController::class, "store"])
            ->name("accounts.store");
        Route::get("accounts", [MerchantAccountController::class, "show"])
            ->name("accounts.show")
            ->middleware('authRole:MERCHANT');
        Route::match(['put', 'patch'], "accounts", [MerchantAccountController::class, "update"])
            ->name("accounts.update")
            ->middleware('authRole:MERCHANT');
    });
