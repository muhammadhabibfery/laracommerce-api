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
    ->middleware(['auth:sanctum', 'verified', 'authRole:MERCHANT'])
    ->group(function () {
        Route::apiResource("accounts", MerchantAccountController::class)
            ->parameters(['accounts' => 'merchantAccount'])
            ->except(['index', 'destroy']);
    });
