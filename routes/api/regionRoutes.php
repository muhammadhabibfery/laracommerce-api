<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegionController;

/*
|--------------------------------------------------------------------------
| Region  API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Region  API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get("region/provinces", [RegionController::class, "getAllProvinces"])->name("region.provinces");
Route::get("region/cities/{id?}", [RegionController::class, "getTheCitiesByProvinceId"])->name("region.cities");
Route::get("region/couriers", [RegionController::class, "getAllCouriers"])->name("region.couriers");
