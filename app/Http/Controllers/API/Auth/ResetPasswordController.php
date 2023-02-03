<?php

namespace App\Http\Controllers\API\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->numbers()
                    ->symbols()
            ],
        ];
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param  Request  $request
     * @param  string  $response
     * @return JsonResponse
     */
    protected function sendResetResponse(Request $request, $response): JsonResponse
    {
        return response()->json([
            'code' => Response::HTTP_OK,
            'message' => trans($response)
        ]);
    }

    /**
     * Get the response for a failed password reset.
     *
     * @param  Request  $request
     * @param  string  $response
     * @return void
     */
    protected function sendResetFailedResponse(Request $request, $response): void
    {
        throw ValidationException::withMessages([
            'email' => [trans($response)]
        ]);
    }
}
