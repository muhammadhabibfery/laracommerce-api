<?php

namespace App\Http\Controllers\API\Auth;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\VerificationRequest;
use Symfony\Component\HttpFoundation\Response;

class VerificationController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  VerificationRequest $request
     * @return JsonResponse
     */
    public function verify(VerificationRequest $request): JsonResponse
    {
        if (!$request->fulfill())
            return $request->throwBadRequestException();

        return response()->json(['code' => Response::HTTP_OK, 'message' => 'Your account succesfully registered.']);
    }

    /**
     * Resend the email verification notification.
     *
     * @param  VerificationRequest $request
     * @return JsonResponse
     */
    public function resend(VerificationRequest $request): JsonResponse
    {
        $user = $this->getUserByEmail($request->validated()['email']);

        if ($user && !$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();

            return response()->json(['code' => Response::HTTP_OK, 'message' => 'Verification link has been sent.']);
        }

        return $request->throwBadRequestException();
    }

    /**
     * query get a user by email.
     *
     * @param  string $email
     * @return User|null
     */
    private function getUserByEmail(string $email): ?User
    {
        return User::where('email', $email)
            ->first();
    }
}
