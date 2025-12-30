<?php

namespace Tests\Unit\App\Notifications;

use App\Models\User;
use App\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function reset_password_notification_can_be_created_with_token()
    {
        $token = 'test-reset-token';

        $notification = new ResetPassword($token);

        $this->assertEquals($token, $notification->token);
    }

    /** @test */
    public function reset_password_notification_uses_session_locale()
    {
        Session::put('locale', 'vi');
        $token = 'test-token';

        $notification = new ResetPassword($token);

        $this->assertEquals('vi', $notification->locale);
    }

    /** @test */
    public function reset_password_notification_can_override_locale()
    {
        Session::put('locale', 'vi');
        $token = 'test-token';

        $notification = new ResetPassword($token, 'en');

        $this->assertEquals('en', $notification->locale);
    }

    /** @test */
    public function reset_password_notification_falls_back_to_default_locale()
    {
        Session::forget('locale');
        $token = 'test-token';

        $notification = new ResetPassword($token);

        $this->assertEquals(config('app.locale'), $notification->locale);
    }

    /** @test */
    public function reset_password_notification_uses_mail_channel()
    {
        $token = 'test-token';
        $user = User::factory()->create();

        $notification = new ResetPassword($token);
        $channels = $notification->via($user);

        $this->assertEquals(['mail'], $channels);
    }

    /** @test */
    public function reset_password_notification_generates_correct_reset_url()
    {
        $token = 'test-reset-token';
        $user = User::factory()->create(['email' => 'test@example.com']);

        $notification = new ResetPassword($token);
        $mailMessage = $notification->toMail($user);

        $this->assertStringContainsString('reset-password', $mailMessage->actionUrl);
        $this->assertStringContainsString($token, $mailMessage->actionUrl);
        // Email is URL-encoded in the URL
        $this->assertTrue(
            str_contains($mailMessage->actionUrl, 'test@example.com') ||
            str_contains($mailMessage->actionUrl, 'test%40example.com')
        );
    }

    /** @test */
    public function user_can_receive_password_reset_notification()
    {
        Notification::fake();

        $user = User::factory()->create();
        $token = 'test-token';

        $user->notify(new ResetPassword($token));

        Notification::assertSentTo($user, ResetPassword::class);
    }

    /** @test */
    public function reset_password_notification_contains_token_in_closure()
    {
        Notification::fake();

        $user = User::factory()->create();
        $token = 'specific-test-token';

        $user->notify(new ResetPassword($token));

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($token) {
            return $notification->token === $token;
        });
    }
}
