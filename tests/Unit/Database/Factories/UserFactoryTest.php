<?php

namespace Tests\Unit\Database\Factories;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFactoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function factory_creates_user_with_required_fields()
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }

    /**
     * @test
     */
    public function factory_generates_unique_emails()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->assertNotEquals($user1->email, $user2->email);
    }

    /**
     * @test
     */
    public function factory_hashes_password()
    {
        $user = User::factory()->create();

        $this->assertNotEquals('password', $user->password);
        $this->assertTrue(strlen($user->password) > 20);
    }

    /**
     * @test
     */
    public function factory_sets_valid_role()
    {
        $user = User::factory()->create();

        $this->assertContains($user->role, ['user', 'admin', 'manager']);
    }

    /**
     * @test
     */
    public function factory_can_override_attributes()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'admin',
        ]);

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('admin', $user->role);
    }

    /**
     * @test
     */
    public function factory_can_create_multiple_users()
    {
        $users = User::factory()->count(5)->create();

        $this->assertCount(5, $users);
        $this->assertEquals(5, User::count());
    }
}
