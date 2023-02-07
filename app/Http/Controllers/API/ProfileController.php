<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\ProfileRequest;
use App\Http\Resources\UserResource;
use App\Traits\Profile;
use ErrorException;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    use Profile;

    private const PROFILE_SUCCESS = 'The profile updated successfully.', PASSWORD_SUCCESS = 'The password updated successfully.',
        FAILED = 'Failed to get service, please try again.';

    /**
     * Edit the user's profile.
     *
     * @param  ProfileRequest $request
     * @return JsonResponse|RedirectResponse
     */
    public function editProfile(ProfileRequest $request): JsonResponse|RedirectResponse
    {
        if ($this->updateProfile($request)) {
            if ($request->wantsJson() || $request->is('/api/*')) {
                $user = (new UserResource($request->user()))
                    ->response()
                    ->getData(true);

                return $this->wrapResponse(Response::HTTP_OK, self::PROFILE_SUCCESS, $user);
            }

            return to_route('dashboard')->with('success', self::PROFILE_SUCCESS);
        }

        throw new ErrorException(self::FAILED, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Change the user's password.
     *
     * @param  mixed $request
     * @return JsonResponse|RedirectResponse
     */
    public function changePassword(ProfileRequest $request): JsonResponse|RedirectResponse
    {
        if ($this->updatePassword($request))
            return $request->wantsJson() || $request->is('api/*')
                ? $this->wrapResponse(Response::HTTP_OK, self::PASSWORD_SUCCESS)
                : to_route('dashboard')->with('success', self::PASSWORD_SUCCESS);

        throw new ErrorException(self::FAILED, Response::HTTP_INTERNAL_SERVER_ERROR);
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

        if (count($resource)) $result = array_merge($result, ['data' => $resource['data']]);

        return response()->json($result);
    }
}
