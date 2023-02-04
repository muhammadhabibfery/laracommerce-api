<?php

namespace Tests\Validations;

use Illuminate\Support\Arr;

trait RegisterValidation
{

    public array $data = [
        'name' => 'John Lennon',
        'username' => 'johnlennon',
        'email' => 'jl@gmail.com',
        'phone' => '081236543123',
        'password' => 'secret@123',
        'password_confirmation' => 'secret@123'
    ];

    /** @test */
    public function all_fields_are_required()
    {
        $this->withExceptionHandling();

        $res = $this->postJson(route('auth.register'), [], $this->header);

        $res->assertUnprocessable()
            ->assertJsonCount(5, 'errors');
    }

    /** @test */
    public function username_email_and_phone_fields_should_be_unique()
    {
        $this->withExceptionHandling();

        $user = $this->createUser(Arr::except($this->data, ['password_confirmation']));

        $res = $this->postJson(
            route('auth.register'),
            $user->toArray(),
            $this->header
        );

        $res->assertUnprocessable()
            ->assertJsonPath('errors.username', ['The username has already been taken.'])
            ->assertJsonPath('errors.email', ['The email has already been taken.'])
            ->assertJsonPath('errors.phone', ['The phone has already been taken.']);
    }

    /** @test */
    public function a_password_field_should_be_follow_the_password_rules()
    {
        $this->withExceptionHandling();

        $res = $this->postJson(
            route('auth.register'),
            ['password' => 'test', 'password_confirmation' => 'test'],
            $this->header
        );

        $res->assertUnprocessable()
            ->assertJsonPath('errors.password.0', 'The password must be at least 8 characters.')
            ->assertJsonPath('errors.password.1', 'The password must contain at least one symbol.')
            ->assertJsonPath('errors.password.2', 'The password must contain at least one number.');
    }
}
