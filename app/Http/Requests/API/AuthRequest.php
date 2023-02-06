<?php

namespace App\Http\Requests\API;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        if ($this->is('api/register'))
            return [
                'name' => ['required', 'min:3', 'max:100'],
                'username' => ['required', 'alpha_dash', 'unique:users,username'],
                'email' => ['required', 'email', 'unique:users,email'],
                'phone' => ['required', 'digits_between:12,13', 'unique:users,phone'],
                'password' => [
                    'required', 'string', 'confirmed',
                    Password::min(8)
                        ->numbers()
                        ->symbols()
                ],
            ];

        if ($this->is('api/login'))
            return [
                'username' => ['required', 'string'],
                'password' => ['required', 'string']
            ];
    }

    /**
     * merge the validated request with additional data
     *
     * @return array
     */
    public function validatedData(): array
    {
        return array_merge(
            $this->validated(),
            [
                'role' => 'CUSTOMER',
                'status' => 'ACTIVE',
                'password' => Hash::make($this->validated()['password'])
            ]
        );
    }
}
