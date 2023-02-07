<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ProfileController;

/*
|--------------------------------------------------------------------------
| Profile API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Profile API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum', 'verified'])
    ->group(function () {
        Route::patch("/profile/update-profile", [ProfileController::class, "editProfile"])
            ->name("profile.update-profile");
        Route::patch("/profile/change-password", [ProfileController::class, "changePassword"])
            ->name("profile.change-password");
    });
