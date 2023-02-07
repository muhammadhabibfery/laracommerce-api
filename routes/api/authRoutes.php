<?php

use App\Http\Controllers\API\Auth\ForgotPasswordController;
use App\Http\Controllers\API\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\Auth\ResetPasswordController;
use App\Http\Controllers\API\Auth\VerificationController;

/*
|--------------------------------------------------------------------------
| Authentication API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Authentication API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post("/register", RegisterController::class)
    ->name("auth.register");
Route::post("/login", [LoginController::class, "login"])
    ->name("auth.login");
Route::post("/logout", [LoginController::class, "logout"])
    ->name("auth.logout")
    ->middleware(['auth:sanctum', 'verified']);

Route::get("/email/verify/{id}/{hash}", [VerificationController::class, "verify"])->middleware('signed')
    ->name("verification.verify");
Route::post("/email/resend", [VerificationController::class, "resend"])->middleware('throttle:6,1')
    ->name("verification.send");

Route::post("/password/email", [ForgotPasswordController::class, "sendResetLinkEmail"])
    ->name("auth.password.send-email");

Route::post("/password/reset", [ResetPasswordController::class, "reset"])
    ->name("auth.password.reset");
