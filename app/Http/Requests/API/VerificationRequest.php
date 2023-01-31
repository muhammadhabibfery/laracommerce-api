<?php

namespace App\Http\Requests\API;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class VerificationRequest extends FormRequest
{
    private const UNAUTHORIZED_MESSAGE = 'The credentials does not match.',
        BADREQUEST_MESSAGE = 'Your account has been verified.';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {

        if ($this->routeIs('verification.verify')) {
            if (!auth()->loginUsingId((int) $this->route('id')))
                throw new AuthenticationException(self::UNAUTHORIZED_MESSAGE);

            if (!hash_equals(sha1($this->user()->getEmailForVerification()), (string) $this->route('hash')))
                throw new AuthorizationException(self::UNAUTHORIZED_MESSAGE);
        }

        return true;
    }

    /**
     * Fulfill the email verification request.
     *
     * @return bool
     */
    public function fulfill(): bool
    {
        if (!$this->user()->hasVerifiedEmail()) {
            $this->user()->markEmailAsVerified();

            event(new Verified($this->user()));

            return true;
        }

        return false;
    }

    /**
     * throw bad request exception.
     *
     * @return void
     */
    public function throwBadRequestException(): void
    {
        throw new BadRequestHttpException(self::BADREQUEST_MESSAGE);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->routeIs('verification.send'))
            return [
                'email' => ['required', 'email']
            ];

        return [
            //
        ];
    }
}
