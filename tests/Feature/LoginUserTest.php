<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\Validations\LoginValidation;

class LoginUserTest extends TestCase
{
    use LoginValidation;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    /** @test */
    public function a_user_can_login()
    {
        $user = $this->createUser(
            array_merge($this->data, ['password' => Hash::make($this->data['password'])])
        );

        $res = $this->postJson(route('auth.login'), $this->data, $this->header);

        $res->assertOk()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message', 'data', 'token'])
            )->assertJsonPath('data.name', $user->name)
            ->assertJsonPath('data.username', $user->username)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.phone', $user->phone);

        $this->assertDatabaseHas(
            'users',
            array_merge(Arr::except($user->toArray(), ['updated_at', 'created_at', 'email_verified_at']), ['role' => json_encode($user->toArray()['role'])])
        )
            ->assertDatabaseCount('users', 1);
    }

    /** @test */
    public function a_user_can_logout()
    {
        $user = $this->authenticatedUser();
        $user->createToken('customer-token', $user->role);

        $res = $this->postJson(route('auth.logout'), [], $this->header);

        $res->assertOk()
            ->assertJsonPath('message', 'Logged out succesfully.');
    }
}
