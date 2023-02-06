<?php

use App\Http\Controllers\API\Auth\ForgotPasswordController;
use App\Http\Controllers\API\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\Auth\ResetPasswordController;
use App\Http\Controllers\API\Auth\VerificationController;
use App\Http\Controllers\API\ProfileController;
use App\Models\User;

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
        Route::post("/profile/update-profile", [ProfileController::class, "editProfile"])
            ->name("profile.update-profile");
        Route::post("/profile/change-password", [ProfileController::class, "changePassword"])
            ->name("profile.change-password");
    });
