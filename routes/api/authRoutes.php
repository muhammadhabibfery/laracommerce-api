<?php

use App\Http\Controllers\API\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\Auth\VerificationController;
use App\Http\Requests\API\VerificationRequest;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::get('/email/verify/{id}/{hash}', function (Request $request) {
//     return $request->all();
//     // $request->fulfill();
//     // return User::find(100);
//     throw new BadRequestHttpException('Your email / account has been verified');
//     return 'vv';
// })->name('verification.verify');

Route::post("/register", RegisterController::class)->name("auth.register");
Route::post("/login", [LoginController::class, "login"])->name("auth.login");

Route::get("/email/verify/{id}/{hash}", [VerificationController::class, "verify"])->middleware('signed')
    ->name("verification.verify");
Route::post("/email/resend", [VerificationController::class, "resend"])->middleware('throttle:6,1')
    ->name("verification.send");
