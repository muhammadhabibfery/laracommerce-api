<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Lang;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use Tests\Validations\ForgotPasswordValidation;

class ForgotPasswordTest extends TestCase
{
    use ForgotPasswordValidation;

    public array $email = ['email' => 'jl@g.com'];

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    /** @test */
    public function a_user_can_send_the_reset_password_link()
    {
        $this->createUser($this->email);

        $res = $this->postJson(route('auth.password.send-email'), $this->email, $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message'])
            )->assertJsonPath('message', Lang::get('passwords.sent'));
    }
}
