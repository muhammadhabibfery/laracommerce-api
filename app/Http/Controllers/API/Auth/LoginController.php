<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\AuthRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    private const LOGIN_SUCCESS = 'Logged in succesfully.',
        LOGIN_FAILED = 'The credentials does not match.';

    public function login(AuthRequest $request)
    {
        $user = $this->getUserByUsername($request->validated('username'));

        if (!$user || !Hash::check($request->validated('password'), $user->password))
            throw ValidationException::withMessages([
                'username' => self::LOGIN_FAILED
            ]);

        $user = (new UserResource($user))
            ->additional(['token' => $user->createToken('customer-token', $user->role)->plainTextToken])
            ->response()
            ->getData(true);


        return $this->wrapResponse(Response::HTTP_OK, self::LOGIN_SUCCESS, $user);
    }

    /**
     * query get a user by username.
     *
     * @param  string $username
     * @return User|null
     */
    private function getUserByUsername(string $username): ?User
    {
        return User::where('username', $username)
            ->orWhere('email', $username)
            ->first();
    }


    /**
     * wrap a result into json response.
     *
     * @param  int $code
     * @param  string $message
     * @param  array $resource
     * @return JsonResponse
     */
    private function wrapResponse(int $code, string $message, array $resource): JsonResponse
    {
        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $resource['data'],
            'token' => $resource['token']
        ]);
    }
}
