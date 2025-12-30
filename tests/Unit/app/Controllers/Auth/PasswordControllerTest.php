<?php

namespace Tests\Unit\App\Controllers\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function user_can_update_password_with_valid_data()
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123!'),
        ]);

        $response = $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertRedirect();
        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
    }

    /**
     * @test
     */
    public function update_password_requires_current_password()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('password.update'), [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'current_password');
    }

    /**
     * @test
     */
    public function update_password_requires_correct_current_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123!'),
        ]);

        $response = $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'WrongPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'current_password');
    }

    /**
     * @test
     */
    public function update_password_requires_password_confirmation()
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123!'),
        ]);

        $response = $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword123!',
        ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'password');
    }

    /**
     * @test
     */
    public function new_password_must_match_confirmation()
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123!'),
        ]);

        $response = $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'DifferentPassword123!',
        ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'password');
    }

    /**
     * @test
     */
    public function new_password_must_meet_complexity_requirements()
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123!'),
        ]);

        $response = $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'OldPassword123!',
            'password' => 'simple',
            'password_confirmation' => 'simple',
        ]);

        $response->assertSessionHasErrorsIn('updatePassword', 'password');
    }

    /**
     * @test
     */
    public function guest_cannot_update_password()
    {
        $response = $this->put(route('password.update'), [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertRedirect(route('login'));
    }

    /**
     * @test
     */
    public function password_update_flashes_success_message()
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123!'),
        ]);

        $response = $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'OldPassword123!',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHas('status');
    }
}
