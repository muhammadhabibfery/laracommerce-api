<?php

namespace Tests\Validations;

trait ProfileValidation
{
    /** @test */
    public function username_email_and_phone_fields_should_be_unique()
    {
        $this->withExceptionHandling();
        $user = $this->authenticatedUser(['username' => 'johnlennon']);
        $user2 = $this->createUser(['username' => 'paulmccarthney']);

        $res = $this->patchJson(
            route('profile.update-profile'),
            array_merge($user->toArray(), ['username' => $user2->username, 'phone' => $user2->phone, 'email' => $user2->email]),
            $this->header
        );

        $res->assertUnprocessable()
            ->assertJsonPath('errors.username', ['The username has already been taken.'])
            ->assertJsonPath('errors.email', ['The email has already been taken.'])
            ->assertJsonPath('errors.phone', ['The phone has already been taken.']);
    }

    /** @test */
    public function a_new_password_field_should_be_follow_the_password_rules()
    {
        $this->withExceptionHandling();
        $this->authenticatedUser(['username' => 'johnlennon']);

        $res = $this->patchJson(
            route('profile.change-password'),
            ['current_password' => 'password@123', 'new_password' => 'abcd', 'new_password_confirmation' => 'abcd'],
            $this->header
        );

        $res->assertUnprocessable()
            ->assertJsonPath('errors.new_password.0', 'The new password must be at least 8 characters.')
            ->assertJsonPath('errors.new_password.1', 'The new password must contain at least one symbol.')
            ->assertJsonPath('errors.new_password.2', 'The new password must contain at least one number.');
    }

    /** @test */
    public function a_new_password_should_be_match_with_new_password_confirmation()
    {
        $this->withExceptionHandling();
        $this->authenticatedUser(['username' => 'johnlennon']);

        $res = $this->patchJson(
            route('profile.change-password'),
            ['current_password' => 'password@123', 'new_password' => 'abcd@12345', 'new_password_confirmation' => 'abcd'],
            $this->header
        );

        $res->assertUnprocessable()
            ->assertJsonPath('errors.new_password.0', 'The new password confirmation does not match.');
    }

    /** @test */
    public function a_current_password_field_must_be_correct()
    {
        $this->withExceptionHandling();
        $this->authenticatedUser(['username' => 'johnlennon']);

        $res = $this->patchJson(
            route('profile.change-password'),
            ['current_password' => 'abcd', 'new_password' => 'abcd@1234', 'new_password_confirmation' => 'abcd@1234'],
            $this->header
        );

        $res->assertUnprocessable()
            ->assertJsonPath('errors.current_password.0', 'The password is incorrect.');
    }

    /** @test */
    public function a_new_password_and_current_password_must_be_different()
    {
        $this->withExceptionHandling();
        $this->authenticatedUser(['username' => 'johnlennon']);

        $res = $this->patchJson(
            route('profile.change-password'),
            ['current_password' => 'password@123', 'new_password' => 'password@123', 'new_password_confirmation' => 'password@123'],
            $this->header
        );

        $res->assertUnprocessable()
            ->assertJsonPath('errors.new_password.0', 'The new password and current password must be different.');
    }
}
