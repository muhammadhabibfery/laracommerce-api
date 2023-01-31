<?php

namespace App\Http\Controllers\API\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\AuthRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{

    private const SUCCESS_MESSAGE = 'Account has been successfully registered, please check your email to verify your account.',
        FAILED_MESSAGE = 'Your account failed to register.';

    /**
     * Handle the user register.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(AuthRequest $request): JsonResponse
    {
        if ($user = User::create($request->validatedData())) {
            event(new Registered($user));

            return response()->json(['code' => Response::HTTP_CREATED, 'message' => self::SUCCESS_MESSAGE,], Response::HTTP_CREATED);
        }

        return response()->json(['code' => 500, 'message' => self::FAILED_MESSAGE], 500);
    }
}
