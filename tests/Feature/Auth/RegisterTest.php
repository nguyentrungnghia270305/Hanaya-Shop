<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake(); // Mock all email sending in this test class
    }

    /**
     * @test
     */
    public function user_can_view_registration_page()
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    /**
     * @test
     */
    public function user_can_register_with_valid_data()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!@',
            'password_confirmation' => 'Password123!@',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertTrue(session()->has('pending_registration'));
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function registration_requires_name()
    {
        $response = $this->post(route('register'), [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * @test
     */
    public function registration_requires_valid_email()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * @test
     */
    public function registration_requires_unique_email()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * @test
     */
    public function registration_requires_password_confirmation()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * @test
     */
    public function password_is_hashed_after_registration()
    {
        $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!@',
            'password_confirmation' => 'Password123!@',
        ]);

        $pendingReg = session('pending_registration');

        $this->assertNotNull($pendingReg);
        $this->assertTrue(Hash::check('Password123!@', $pendingReg['password']));
    }

    /**
     * @test
     */
    public function registration_stores_data_in_session_for_verification()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!@',
            'password_confirmation' => 'Password123!@',
        ]);

        $this->assertTrue(session()->has('pending_registration'));
        $pendingReg = session('pending_registration');
        $this->assertEquals('Test User', $pendingReg['name']);
        $this->assertEquals('test@example.com', $pendingReg['email']);
        $this->assertNotEmpty($pendingReg['verification_token']);
    }

    /**
     * @test
     */
    public function registration_redirects_to_verification_notice()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!@',
            'password_confirmation' => 'Password123!@',
        ]);

        $response->assertRedirect(route('verification.notice'));
    }

    /**
     * @test
     */
    public function verification_notice_displays_when_pending_registration_exists()
    {
        session([
            'pending_registration' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'verification_token' => 'test-token',
                'created_at' => now(),
            ],
        ]);

        $response = $this->get(route('verification.notice'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.verification-notice');
    }

    /**
     * @test
     */
    public function verification_notice_redirects_without_pending_registration()
    {
        $response = $this->get(route('verification.notice'));

        $response->assertRedirect(route('register'));
    }

    /**
     * @test
     */
    public function email_verification_creates_user_with_valid_token()
    {
        session([
            'pending_registration' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => Hash::make('Password123!@'),
                'verification_token' => 'valid-token-123',
                'created_at' => now(),
            ],
        ]);

        $response = $this->get(route('verification.verify', ['token' => 'valid-token-123']));

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);
        $this->assertAuthenticated();
    }

    /**
     * @test
     */
    public function email_verification_fails_with_invalid_token()
    {
        session([
            'pending_registration' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => Hash::make('Password123!@'),
                'verification_token' => 'valid-token',
                'created_at' => now(),
            ],
        ]);

        $response = $this->get(route('verification.verify', ['token' => 'invalid-token']));

        $response->assertRedirect(route('register'));
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    /**
     * @test
     */
    public function email_verification_fails_with_expired_token()
    {
        session([
            'pending_registration' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => Hash::make('Password123!@'),
                'verification_token' => 'expired-token',
                'created_at' => now()->subHours(25), // 25 hours ago - expired
            ],
        ]);

        $response = $this->get(route('verification.verify', ['token' => 'expired-token']));

        $response->assertRedirect(route('register'));
        $response->assertSessionHas('error');
        $this->assertFalse(session()->has('pending_registration'));
    }

    /**
     * @test
     */
    public function password_must_contain_lowercase_letter()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'PASSWORD123!',
            'password_confirmation' => 'PASSWORD123!',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * @test
     */
    public function password_must_contain_uppercase_letter()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123!',
            'password_confirmation' => 'password123!',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * @test
     */
    public function password_must_contain_digit()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password!@#',
            'password_confirmation' => 'Password!@#',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * @test
     */
    public function password_must_contain_special_character()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * @test
     */
    public function password_must_not_contain_whitespace()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Pass word123!',
            'password_confirmation' => 'Pass word123!',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * @test
     */
    public function password_must_be_at_least_8_characters()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Pass1!',
            'password_confirmation' => 'Pass1!',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * @test
     */
    public function email_must_be_lowercase()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'TEST@EXAMPLE.COM',
            'password' => 'Password123!@',
            'password_confirmation' => 'Password123!@',
        ]);

        // Should fail with validation error for uppercase email
        $response->assertSessionHasErrors('email');
    }
}
