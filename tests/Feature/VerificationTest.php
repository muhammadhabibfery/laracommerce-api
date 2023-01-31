<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Http\Middleware\ValidateSignature;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Validations\VerificationValidation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;

class VerificationTest extends TestCase
{
    use RefreshDatabase, VerificationValidation;

    public array $userData;
    public User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $this->withoutMiddleware(ValidateSignature::class);

        $this->user = $this->createUser(['email_verified_at' => null]);

        $this->userData = [
            'id' => $this->user->id,
            'hash' => sha1($this->user->email)
        ];
    }

    /** @test */
    public function a_user_can_verify()
    {
        $res = $this->getJson(route('verification.verify', $this->userData));

        $res->assertOk()
            ->assertJsonPath('message', 'Your account succesfully registered.');
    }

    /** @test */
    public function a_user_can_resend_verify_link()
    {
        $res = $this->postJson(route('verification.send'), ['email' => $this->user->email]);

        $res->assertOk()
            ->assertJsonPath('message', 'Verification link has been sent.');
    }

    /** @test */
    public function the_registered_event_should_be_sync_with_listeners()
    {
        $this->postJson(route('verification.send'), ['email' => $this->user->email]);

        Event::fake();
        Event::assertListening(Registered::class, SendEmailVerificationNotification::class);
    }
}
