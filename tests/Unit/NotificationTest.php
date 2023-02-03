<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Notifications\VerifyEmails;
use App\Notifications\ResetPasswords;
use Illuminate\Support\Facades\Notification;

class NotificationTest extends TestCase
{
    public User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser(['email_verified_at' => null]);
    }

    /** @test */
    public function the_verify_emails_notification_should_be_sent()
    {
        Notification::fake();
        $this->user->notify(new VerifyEmails);

        Notification::assertSentTo($this->user, VerifyEmails::class);
    }

    /** @test */
    public function a_reset_passwords_notification_should_be_sent()
    {
        Notification::fake();

        $this->user->notify(new ResetPasswords(self::RESET_PASSWORD_TOKEN));

        Notification::assertSentTo($this->user, ResetPasswords::class);
    }
}
