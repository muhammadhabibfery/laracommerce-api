<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use Tests\Validations\RegisterValidation as ValidationTest;

class RegisterUserTest extends TestCase
{
    use RefreshDatabase, ValidationTest;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    /** @test */
    public function a_user_can_register()
    {
        $res = $this->postJson(route('auth.register'), $this->data);

        $res->assertCreated()
            ->assertJson(
                fn (AssertableJson $json) =>
                $json->hasAll(['code', 'message'])
            );

        $this->assertDatabaseCount('users', 1)
            ->assertDatabaseHas('users', Arr::except($this->data, ['password', 'password_confirmation']));
    }

    /** @test */
    public function the_event_should_dispatched()
    {
        $this->postJson(route('auth.register'), $this->data);

        Event::fake();
        Event::assertListening(Registered::class, SendEmailVerificationNotification::class);
    }
}
