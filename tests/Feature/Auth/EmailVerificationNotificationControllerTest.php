<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailVerificationNotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email_verified_at' => null,
        ]);
    }

    /**
     * @test
     */
    public function user_can_request_verification_email()
    {
        Notification::fake();

        $response = $this->actingAs($this->user)->post(route('verification.send'));

        $response->assertRedirect();
        $response->assertSessionHas('status', 'verification-link-sent');
    }

    /**
     * @test
     */
    public function already_verified_user_is_redirected()
    {
        $this->user->email_verified_at = now();
        $this->user->save();

        $response = $this->actingAs($this->user)->post(route('verification.send'));

        $response->assertRedirect(route('dashboard'));
    }

    /**
     * @test
     */
    public function guest_cannot_request_verification_email()
    {
        $response = $this->post(route('verification.send'));

        $response->assertRedirect(route('login'));
    }

    /**
     * @test
     */
    public function verification_email_is_sent_to_user()
    {
        Notification::fake();

        $this->actingAs($this->user)->post(route('verification.send'));

        Notification::assertSentTo(
            $this->user,
            \Illuminate\Auth\Notifications\VerifyEmail::class
        );
    }

    /**
     * @test
     */
    public function multiple_verification_requests_are_allowed()
    {
        Notification::fake();

        $this->actingAs($this->user)->post(route('verification.send'));
        $this->actingAs($this->user)->post(route('verification.send'));

        Notification::assertSentTo(
            $this->user,
            \Illuminate\Auth\Notifications\VerifyEmail::class
        );
    }

    /**
     * @test
     */
    public function verification_link_sent_status_is_stored_in_session()
    {
        Notification::fake();

        $response = $this->actingAs($this->user)->post(route('verification.send'));

        $response->assertSessionHas('status', 'verification-link-sent');
    }

    /**
     * @test
     */
    public function admin_with_unverified_email_can_request_verification()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => null,
        ]);

        Notification::fake();

        $response = $this->actingAs($admin)->post(route('verification.send'));

        $response->assertRedirect();
        Notification::assertSentTo($admin, \Illuminate\Auth\Notifications\VerifyEmail::class);
    }

    /**
     * @test
     */
    public function verified_admin_is_redirected_to_admin_dashboard()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('verification.send'));

        $response->assertRedirect(route('dashboard'));
    }
}
