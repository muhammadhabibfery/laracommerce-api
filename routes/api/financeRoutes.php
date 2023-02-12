<?php

use App\Http\Controllers\API\FinanceController;
use App\Http\Controllers\API\WithDrawController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Finance API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Finance API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('merchant')
    ->middleware(['auth:sanctum', 'verified', 'authRole:MERCHANT'])
    ->group(function () {
        Route::apiResource("finances", FinanceController::class)
            ->only('index');
        Route::get("finances/withdraw", [WithDrawController::class, "create"])->name("finance.wd");
        Route::post("finances/withdraw", [WithDrawController::class, "store"])->name("finance.wd");
    });
