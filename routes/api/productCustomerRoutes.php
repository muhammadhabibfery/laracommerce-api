<?php

use App\Http\Controllers\API\ProductCustomerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Product Customer API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Product Customer API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get("merchant/detail/{merchantAccount}", [ProductCustomerController::class, "getMerchant"])->name("products.merchant");
Route::get("product/search", [ProductCustomerController::class, "searchProducts"])->name("products.search");
Route::get("product/{product}", [ProductCustomerController::class, "getProduct"])->name("products.detail");
