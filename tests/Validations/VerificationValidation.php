<?php

namespace Tests\Validations;

trait VerificationValidation
{

    public string $unauthorizedMessage = 'The credentials does not match.',
        $badRequestMessage = 'Your account has been verified.';

    /** @test */
    public function a_user_should_be_authenticated()
    {
        $this->withExceptionHandling();

        $res = $this->getJson(route('verification.verify', ['id' => '1000', 'hash' => '123',]));

        $res->assertUnauthorized()
            ->assertJsonPath('message', $this->unauthorizedMessage);
    }

    /** @test */
    public function the_hash_parameters_should_be_match()
    {
        $this->withExceptionHandling();

        $res = $this->getJson(route(
            'verification.verify',
            ['id' => (string) $this->user->id, 'hash' => '123']
        ));

        $res->assertForbidden()
            ->assertJsonPath('message', $this->unauthorizedMessage);
    }

    /** @test */
    public function a_verified_user_cannot_resend_verify_link()
    {
        $this->withExceptionHandling();

        $user = $this->createUser();

        $res = $this->getJson(
            route(
                'verification.verify',
                ['id' => $user->id, 'hash' => sha1($user->email)]
            )
        );

        $res->assertStatus(400)
            ->assertJsonPath('message', $this->badRequestMessage);
    }
}
