<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ConfirmablePasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);
    }

    /**
     * @test
     */
    public function authenticated_user_can_view_password_confirmation_screen()
    {
        $response = $this->actingAs($this->user)->get(route('password.confirm'));

        $response->assertOk();
        $response->assertViewIs('auth.confirm-password');
    }

    /**
     * @test
     */
    public function guest_cannot_view_password_confirmation_screen()
    {
        $response = $this->get(route('password.confirm'));

        $response->assertRedirect(route('login'));
    }

    /**
     * @test
     */
    public function user_can_confirm_password_with_correct_password()
    {
        $response = $this->actingAs($this->user)->post(route('password.confirm'), [
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    /**
     * @test
     */
    public function user_cannot_confirm_with_incorrect_password()
    {
        $response = $this->actingAs($this->user)->post(route('password.confirm'), [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * @test
     */
    public function password_confirmation_requires_password_field()
    {
        $response = $this->actingAs($this->user)->post(route('password.confirm'), [
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * @test
     */
    public function password_confirmation_stores_timestamp_in_session()
    {
        $response = $this->actingAs($this->user)->post(route('password.confirm'), [
            'password' => 'password123',
        ]);

        $this->assertNotNull(session('auth.password_confirmed_at'));
    }

    /**
     * @test
     */
    public function guest_cannot_confirm_password()
    {
        $response = $this->post(route('password.confirm'), [
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('login'));
    }

    /**
     * @test
     */
    public function password_confirmation_redirects_to_intended_url()
    {
        $response = $this->actingAs($this->user)
            ->withSession(['url.intended' => route('profile.edit')])
            ->post(route('password.confirm'), [
                'password' => 'password123',
            ]);

        $response->assertRedirect(route('profile.edit'));
    }

    /**
     * @test
     */
    public function password_confirmation_fails_with_validation_error_message()
    {
        $response = $this->actingAs($this->user)->post(route('password.confirm'), [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertEquals(
            __('auth.password'),
            session('errors')->get('password')[0]
        );
    }

    /**
     * @test
     */
    public function confirmed_password_remains_valid_for_configured_time()
    {
        $this->actingAs($this->user)->post(route('password.confirm'), [
            'password' => 'password123',
        ]);

        $this->assertTrue(
            session('auth.password_confirmed_at') <= now()->timestamp
        );
    }

    /**
     * @test
     */
    public function password_confirmation_works_with_hashed_passwords()
    {
        $userWithBcrypt = User::factory()->create([
            'password' => bcrypt('test-password'),
        ]);

        $response = $this->actingAs($userWithBcrypt)->post(route('password.confirm'), [
            'password' => 'test-password',
        ]);

        $response->assertSessionHasNoErrors();
    }
}
