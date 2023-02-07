<?php

namespace App\Http\Requests\API;

use App\Traits\ImageHandler;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
{
    use ImageHandler;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     *
     */
    public function rules(): array
    {
        if ($this->is('api/profile/update-profile'))
            return [
                'name' => ['required', 'min:3', 'max:100'],
                'username' => ['required', 'alpha_dash', Rule::unique('users', 'username')->ignore($this->user())],
                'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user())],
                'phone' => ['required', 'digits_between:12,13', Rule::unique('users', 'phone')->ignore($this->user())],
                'address' => ['nullable', 'string'],
                'avatar' => ['image', 'max:2500', 'nullable']
            ];

        if ($this->is('api/profile/change-password'))
            return [
                'current_password' => ['required', 'string', 'current_password'],
                'new_password' => [
                    'required', 'string', 'confirmed', 'different:current_password',
                    Password::min(8)
                        ->numbers()
                        ->symbols()
                ],
                'new_password_confirmation' => ['required']
            ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $request = $this->request->all();

        if ($this->is('api/profile/change-password') && $this->has('currentPassword') && $this->has('newPassword') && $this->has('newPasswordConfirmation')) {
            $this->merge([
                'current_password' => $request['currentPassword'],
                'new_password' => $request['newPassword'],
                'new_password_confirmation' => $request['newPasswordConfirmation']
            ]);
        }
    }

    /**
     * merge the validated request with additional data
     *
     * @return array
     */
    public function validatedProfile(): array
    {
        return array_merge(
            $this->validated(),
            ['avatar' => $this->setImageFile($this, 'avatars', $this->user()->avatar)]
        );
    }

    /**
     * Hash the new password
     *
     * @return array
     */
    public function validatedPassword(): array
    {
        return ['password' => Hash::make($this->validated()['new_password'])];
    }
}
