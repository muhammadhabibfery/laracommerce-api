<?php

namespace App\Http\Controllers\API\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    /**
     * Get the response for a successful password reset link.
     *
     * @param  Request  $request
     * @param  string  $response
     * @return JsonResponse
     */
    protected function sendResetLinkResponse(Request $request, $response): JsonResponse
    {
        return response()->json([
            'code' => Response::HTTP_OK,
            'message' => trans($response)
        ]);
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param  Request  $request
     * @param  string  $response
     *
     * @return void
     */
    protected function sendResetLinkFailedResponse(Request $request, $response): void
    {
        throw ValidationException::withMessages([
            'email' => [trans($response)],
        ]);
    }
}
