<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Password;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use Tests\Validations\ResetPasswordValidation;

class ResetPasswordTest extends TestCase
{
    use ResetPasswordValidation;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    /** @test */
    public function a_user_can_reset_password()
    {
        $user = $this->createUser(['email' => 'johnlennon@gmail.com']);
        $token = Password::createToken($user);

        $res = $this->postJson(
            route('auth.password.reset'),
            [
                'email' => 'johnlennon@gmail.com',
                'password' => 'aaaaa@12',
                'password_confirmation' => 'aaaaa@12',
                'token' => $token
            ]
        );

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message'])
            )
            ->assertJsonPath('message', Lang::get('passwords.reset'));

        $this->assertTrue(Hash::check('aaaaa@12', $user->fresh()->password));
        $this->assertDatabaseMissing('password_resets', [
            'email' => $user->email,
            'token' => $user->token
        ]);
    }
}
