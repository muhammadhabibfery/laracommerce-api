<?php

namespace Tests\Validations;

trait LoginValidation
{

    public array $data = [
        'username' => 'johnlennon',
        'password' => 'secret@123',
    ];

    /** @test */
    public function all_fields_are_required()
    {
        $this->withExceptionHandling();

        $res = $this->postJson(route('auth.login'), [], $this->header);

        $res->assertUnprocessable()
            ->assertInvalid(['username', 'password'])
            ->assertJsonCount(2, 'errors');
    }

    /** @test */
    public function the_credentials_does_not_match()
    {
        $this->createUser();

        $this->withExceptionHandling();

        $res = $this->postJson(route('auth.login'), $this->data, $this->header);

        $res->assertUnprocessable()
            ->assertInvalid(['username'])
            ->assertJsonPath('errors.username.0', 'The credentials does not match.')
            ->assertJsonCount(1, 'errors');
    }

    /** @test */
    public function unautheticated_user_cannot_access_authenticated_routes()
    {
        $this->withExceptionHandling();

        $res = $this->postJson(route('auth.logout'), [], $this->header);

        $res->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }
}
