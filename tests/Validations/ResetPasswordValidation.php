<?php

namespace Tests\Validations;

use Illuminate\Support\Facades\Lang;

trait ResetPasswordValidation
{

    /** @test */
    public function the_email_fied_should_be_an_email_format()
    {
        $this->withExceptionHandling();

        $res = $this->postJson(route('auth.password.reset'), [
            'email' => 'abc',
            'password' => 'aaaaa@12',
            'password_confirmation' => 'aaaaa@12',
            'token' => 'abc'
        ], $this->header);

        $res->assertUnprocessable()
            ->assertJsonPath('errors.email', ['The email must be a valid email address.']);
    }

    /** @test */
    public function the_email_fied_must_have_an_existing_user()
    {
        $this->createUser(['email' => 'johnlennon@gmail.com']);

        $this->withExceptionHandling();

        $res = $this->postJson(route('auth.password.reset'), [
            'email' => 'paulmccarthney@gmail.com',
            'password' => 'aaaaa@12',
            'password_confirmation' => 'aaaaa@12',
            'token' => 'abc'
        ], $this->header);

        $res->assertUnprocessable()
            ->assertJsonPath('errors.email', [Lang::get('passwords.user')]);
    }

    /** @test */
    public function a_password_field_should_be_follow_the_password_rules()
    {
        $this->createUser(['email' => 'johnlennon@gmail.com']);
        $this->withExceptionHandling();

        $res = $this->postJson(
            route('auth.password.reset'),
            [
                'email' => 'paulmccarthney@gmail.com',
                'password' => 'abc',
                'password_confirmation' => 'abc',
                'token' => 'abc'
            ],
            $this->header
        );

        $res->assertUnprocessable()
            ->assertJsonPath('errors.password.0', 'The password must be at least 8 characters.')
            ->assertJsonPath('errors.password.1', 'The password must contain at least one symbol.')
            ->assertJsonPath('errors.password.2', 'The password must contain at least one number.');
    }

    /** @test */
    public function a_token_field_should_be_match()
    {
        $this->createUser(['email' => 'johnlennon@gmail.com']);
        $this->withExceptionHandling();

        $res = $this->postJson(
            route('auth.password.reset'),
            [
                'email' => 'johnlennon@gmail.com',
                'password' => 'aaaaa@12',
                'password_confirmation' => 'aaaaa@12',
                'token' => 'abc'
            ],
            $this->header
        );

        $res->assertUnprocessable()
            ->assertJsonPath('errors.email', [Lang::get('passwords.token')]);
    }
}
