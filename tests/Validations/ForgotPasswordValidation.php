<?php

namespace Tests\Validations;

use Illuminate\Support\Facades\Lang;

trait ForgotPasswordValidation
{

    /** @test */
    public function the_email_fied_should_be_an_email_format()
    {
        $this->withExceptionHandling();

        $res = $this->postJson(route('auth.password.send-email'), ['email' => 'abc'], $this->header);

        $res->assertUnprocessable()
            ->assertJsonPath('errors.email', ['The email must be a valid email address.'])
            ->assertJsonCount(1, 'errors');
    }

    /** @test */
    public function the_email_fied_must_have_an_existing_user()
    {
        $this->createUser(['email' => 'johnlennon@gmail.com']);

        $this->withExceptionHandling();

        $res = $this->postJson(route('auth.password.send-email'), $this->email, $this->header);

        $res->assertUnprocessable()
            ->assertJsonPath('errors.email', [Lang::get('passwords.user')])
            ->assertJsonCount(1, 'errors');
    }
}
