<?php

namespace Tests\Feature\Routes;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthRoutesIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function register_route_exists()
    {
        $this->assertTrue(Route::has('register'));
    }

    /** @test */
    public function login_route_exists()
    {
        $this->assertTrue(Route::has('login'));
    }

    /** @test */
    public function logout_route_exists()
    {
        $this->assertTrue(Route::has('logout'));
    }

    /** @test */
    public function password_reset_routes_exist()
    {
        $this->assertTrue(Route::has('password.request'));
        $this->assertTrue(Route::has('password.email'));
        $this->assertTrue(Route::has('password.reset'));
        $this->assertTrue(Route::has('password.update'));
    }

    /** @test */
    public function guest_can_access_register_page()
    {
        $response = $this->get(route('register'));
        $response->assertOk();
    }

    /** @test */
    public function guest_can_access_login_page()
    {
        $response = $this->get(route('login'));
        $response->assertOk();
    }

    /** @test */
    public function guest_can_access_password_reset_page()
    {
        $response = $this->get(route('password.request'));
        $response->assertOk();
    }

    /** @test */
    public function authenticated_users_cannot_access_guest_routes()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('register'));
        $response->assertRedirect('/');
    }

    /** @test */
    public function verification_notice_route_exists()
    {
        $this->assertTrue(Route::has('verification.notice'));
    }

    /** @test */
    public function verification_resend_route_exists()
    {
        $this->assertTrue(Route::has('verification.resend'));
    }

    /** @test */
    public function password_confirmation_route_exists()
    {
        $this->assertTrue(Route::has('password.confirm'));
    }

    /** @test */
    public function user_can_access_verification_notice()
    {
        // verification.notice is for guests with pending registration
        session([
            'pending_registration' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'verification_token' => 'test-token',
                'created_at' => now(),
            ],
        ]);

        $response = $this->get(route('verification.notice'));
        $response->assertOk();
    }

    /** @test */
    public function user_can_resend_verification_email()
    {
        Notification::fake();
        Mail::fake(); // Mock Mail sending to avoid Gmail limits

        // verification.resend is for guests with pending registration
        session([
            'pending_registration' => [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'verification_token' => 'test-token',
                'created_at' => now(),
            ],
        ]);

        $response = $this->post(route('verification.resend'));
        $response->assertRedirect();
    }

    /** @test */
    public function authenticated_user_can_access_password_confirm()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('password.confirm'));
        $response->assertOk();
    }

    /** @test */
    public function authenticated_user_can_update_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123!@'),
        ]);

        $response = $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'OldPassword123!@',
            'password' => 'NewPassword123!@',
            'password_confirmation' => 'NewPassword123!@',
        ]);

        $response->assertSessionHasNoErrors();
    }

    /** @test */
    public function user_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));
        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
