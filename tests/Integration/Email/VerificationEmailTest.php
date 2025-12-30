<?php

namespace Tests\Integration\Email;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class VerificationEmailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function verification_email_is_sent_to_new_user()
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $user->sendEmailVerificationNotification();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /**
     * @test
     */
    public function verification_email_contains_valid_url()
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $user->sendEmailVerificationNotification();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /**
     * @test
     */
    public function verified_user_does_not_receive_verification_email()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->assertNotNull($user->email_verified_at);
    }

    /**
     * @test
     */
    public function verification_notification_can_be_resent()
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $user->sendEmailVerificationNotification();
        $user->sendEmailVerificationNotification();

        Notification::assertSentTo($user, VerifyEmail::class);
        Notification::assertCount(2);
    }
}
