<?php

use App\Http\Controllers\API\CategoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Category API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Category API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get("categories", [CategoryController::class, "index"])->name("categories");
Route::get("categories/{category}", [CategoryController::class, "show"])->name("categories.show");
