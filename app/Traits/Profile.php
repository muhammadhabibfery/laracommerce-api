<?php

namespace App\Traits;

use App\Http\Requests\API\ProfileRequest;

trait Profile
{
    use ImageHandler;

    /**
     * Update the user's profile.
     *
     * @param  ProfileRequest $request
     * @return bool
     */
    public function updateProfile(ProfileRequest $request): bool
    {
        if ($request->user()->update($request->validatedProfile())) {
            $this->createImage($request, $request->user()->avatar, 'avatars');
            return true;
        }

        return false;
    }

    /**
     * Update the user's password.
     *
     * @param  ProfileRequest $request
     * @return bool
     */
    public function updatePassword(ProfileRequest $request): bool
    {
        return $request->user()->update($request->validatedPassword()) ? true : false;
    }
}
