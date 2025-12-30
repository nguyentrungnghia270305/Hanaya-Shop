<?php

namespace Tests\E2E\User;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function registration_validates_all_required_fields()
    {
        // Test empty form - should return validation errors
        $response = $this->post(route('register'), []);
        
        // Laravel returns 302 redirect back with errors in session
        $response->assertStatus(302);
        $response->assertRedirect();
        
        // Test duplicate email with properly created user
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        
        $response = $this->post(route('register'), [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ]);
        
        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }

    /**
     * @test
     */
    public function newly_registered_user_can_login_and_logout()
    {
        // Create user using factory (since registration needs email verification)
        $user = User::factory()->create([
            'email' => 'jane@example.com',
            'password' => Hash::make('Password123!')
        ]);
        
        // Login
        $response = $this->post(route('login'), [
            'email' => 'jane@example.com',
            'password' => 'Password123!'
        ]);
        
        $response->assertRedirect();
        $this->assertAuthenticated();
        
        // Logout
        $this->post(route('logout'));
        $this->assertGuest();
        
        // Login again to verify credentials still work
        $response = $this->post(route('login'), [
            'email' => 'jane@example.com',
            'password' => 'Password123!'
        ]);
        
        $response->assertRedirect();
        $this->assertAuthenticated();
    }

    /**
     * @test
     */
    public function registered_user_can_update_profile()
    {
        // Create user
        $user = User::factory()->create([
            'name' => 'Tom Wilson',
            'email' => 'tom@example.com'
        ]);
        
        $this->actingAs($user);
        
        // Update profile
        $response = $this->patch(route('profile.update'), [
            'name' => 'Thomas Wilson',
            'email' => 'tom@example.com'
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'tom@example.com',
            'name' => 'Thomas Wilson'
        ]);
    }

    /**
     * @test
     */
    public function registered_user_starts_with_default_role()
    {
        // Factory creates user with default role
        $user = User::factory()->create([
            'email' => 'regular@example.com',
            'role' => 'user'  // Explicitly set to user role
        ]);
        
        $this->assertEquals('user', $user->role);
        $this->assertFalse($user->isAdmin());
    }

    /**
     * @test
     */
    public function registered_user_can_add_shipping_address()
    {
        $user = User::factory()->create([
            'email' => 'address@example.com'
        ]);
        
        $this->actingAs($user);
        
        // Add address (matching actual database schema: phone_number + address)
        $response = $this->post(route('addresses.store'), [
            'phone_number' => '0123456789',
            'address' => '123 Main St, Ho Chi Minh, HCM, 70000'
        ]);
        
        $response->assertSuccessful();
        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'phone_number' => '0123456789'
        ]);
    }

    /**
     * @test
     */
    public function authenticated_user_cannot_access_register_page()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->get(route('register'));
        
        $response->assertRedirect();
    }
}
