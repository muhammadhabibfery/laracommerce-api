<?php

use App\Http\Controllers\API\LandingPageController;
use App\Http\Controllers\API\RegionController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/dashboard', function () {
    return request()->user();
});

Route::get("/", [LandingPageController::class, "index"])->name("landing-page");

Route::get("test-provinces", [RegionController::class, "getAllProvinces"])->name("test-provinces");
Route::get("test-cities/{id?}", [RegionController::class, "getTheCitiesByProvinceId"])->name("test-cities");
Route::get("test-couriers", [RegionController::class, "getAllCouriers"])->name("test-couriers");

$routes = glob(__DIR__ . "/api/*.php");
foreach ($routes as $route) require($route);
