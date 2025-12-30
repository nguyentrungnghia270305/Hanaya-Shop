<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function email_verification_screen_can_be_rendered()
    {
        // verification.notice requires pending_registration in session
        session([
            'pending_registration' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('Password123!@'),
                'verification_token' => 'test-token',
                'created_at' => now(),
            ],
        ]);

        $response = $this->get(route('verification.notice'));

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function email_can_be_verified()
    {
        // Store pending registration in session
        $password = bcrypt('Password123!@');
        session([
            'pending_registration' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => $password,
                'verification_token' => 'valid-token',
                'created_at' => now(),
            ],
        ]);

        $response = $this->get(route('verification.verify', ['token' => 'valid-token']));

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

        $this->assertAuthenticated();
    }

    /**
     * @test
     */
    public function email_is_not_verified_with_invalid_hash()
    {
        session([
            'pending_registration' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('Password123!@'),
                'verification_token' => 'valid-token',
                'created_at' => now(),
            ],
        ]);

        $response = $this->get(route('verification.verify', ['token' => 'invalid-token']));

        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function verified_user_is_redirected_to_home()
    {
        // When accessing verification.notice without pending registration
        $response = $this->get(route('verification.notice'));

        $response->assertRedirect(route('register'));
    }
}
