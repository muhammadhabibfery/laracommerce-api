<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;

class EventTest extends TestCase
{
    public User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser(['email_verified_at' => null]);
    }

    /** @test */
    public function the_registered_event_should_be_dispatched_and_sync_with_listeners()
    {
        Event::fake();
        Event::dispatch(new Registered($this->user));

        Event::assertDispatched(Registered::class);
        Event::assertListening(Registered::class, SendEmailVerificationNotification::class);
    }

    /** @test */
    public function the_verified_event_should_be_dispatched()
    {
        Event::fake();
        Event::dispatch(new Verified($this->user));

        Event::assertDispatched(Verified::class);
    }

    /** @test */
    public function the_password_reset_event_should_be_dispatched()
    {
        Event::fake();
        Event::dispatch(new PasswordReset($this->user));

        Event::assertDispatched(PasswordReset::class);
    }
}
