<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use Tests\Validations\ProfileValidation;

class ProfileTest extends TestCase
{
    use ProfileValidation;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    /** @test */
    public function a_user_can_update_profile()
    {
        $user = $this->authenticatedUser(['username' => 'johnlennon']);

        $res = $this->patchJson(
            route('profile.update-profile'),
            array_merge($user->toArray(), ['name' => 'john lennon']),
            $this->header
        );

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data'])
            )
            ->assertJsonPath('data.name', 'John Lennon');

        $this->assertDatabaseHas('users', ['name' => 'John Lennon']);
    }

    /** @test */
    public function a_user_can_change_password()
    {
        $this->authenticatedUser();

        $res = $this->patchJson(
            route('profile.change-password'),
            ['current_password' => 'password@123', 'new_password' => 'password@1234', 'new_password_confirmation' => 'password@1234'],
            $this->header
        );

        $res->assertOk()
            ->assertJsonPath('message', 'The password updated successfully.');
    }
}
