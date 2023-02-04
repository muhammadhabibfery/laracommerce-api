<?php

namespace App\Http\Controllers\API\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\API\AuthRequest;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    private const LOGIN_SUCCESS = 'Logged in succesfully.',
        LOGIN_FAILED = 'The credentials does not match.',
        LOGOUT_SUCCESS = 'Logged out succesfully.';

    /**
     * Handle login user.
     *
     * @param AuthRequest $request
     * @return JsonResponse
     */
    public function login(AuthRequest $request): JsonResponse
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
     * Handle logout user.
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        if ($request->user()->tokens()->delete()) return $this->wrapResponse(Response::HTTP_OK, self::LOGOUT_SUCCESS);
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
    private function wrapResponse(int $code, string $message, ?array $resource = []): JsonResponse
    {
        $result = [
            'code' => $code,
            'message' => $message
        ];

        if (count($resource))
            $result = array_merge(
                $result,
                [
                    'data' => $resource['data'],
                    'token' => $resource['token']
                ]
            );

        return response()->json($result);
    }
}
