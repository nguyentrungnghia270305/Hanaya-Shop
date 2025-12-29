<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function user_can_view_password_reset_request_form()
    {
        $response = $this->get(route('password.request'));
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.forgot-password');
    }

    /**
     * @test
     */
    // public function user_can_request_password_reset_link()
    // {
    //     Mail::fake(); // Mock email sending
        
    //     $user = User::factory()->create(['email' => 'test@example.com']);
        
    //     $response = $this->post(route('password.email'), [
    //         'email' => 'test@example.com'
    //     ]);
        
    //     $response->assertSessionHas('status');
    // }

    /**
     * @test
     */
    public function password_reset_link_not_sent_for_invalid_email()
    {
        $response = $this->post(route('password.email'), [
            'email' => 'nonexistent@example.com'
        ]);
        
        $response->assertSessionHasErrors('email');
    }

    /**
     * @test
     */
    // public function user_can_reset_password_with_valid_token()
    // {
    //     Mail::fake(); // Mock email sending
        
    //     $user = User::factory()->create();
    //     $token = Password::createToken($user);
        
    //     $response = $this->post(route('password.store'), [
    //         'token' => $token,
    //         'email' => $user->email,
    //         'password' => 'newpassword123',
    //         'password_confirmation' => 'newpassword123'
    //     ]);
        
    //     $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    // }

    /**
     * @test
     */
    public function password_reset_requires_valid_email()
    {
        $response = $this->post(route('password.email'), [
            'email' => 'invalid-email'
        ]);
        
        $response->assertSessionHasErrors('email');
    }
}
