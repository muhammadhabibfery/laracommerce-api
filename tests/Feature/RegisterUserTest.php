<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Arr;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Validations\RegisterValidation as ValidationTest;

class RegisterUserTest extends TestCase
{
    use ValidationTest;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    /** @test */
    public function a_user_can_register()
    {
        $res = $this->postJson(route('auth.register'), $this->data, $this->header);

        $res->assertCreated()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message'])
            );

        $this->assertDatabaseCount('users', 1)
            ->assertDatabaseHas('users', Arr::except($this->data, ['password', 'password_confirmation']));
    }
}
