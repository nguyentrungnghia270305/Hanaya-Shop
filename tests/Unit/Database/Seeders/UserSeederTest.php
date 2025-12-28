<?php

namespace Tests\Unit\Database\Seeders;

use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function user_seeder_runs_successfully()
    {
        $seeder = new UserSeeder();
        
        $this->assertNull($seeder->run());
    }

    /**
     * @test
     */
    public function user_seeder_creates_users()
    {
        $this->seed(UserSeeder::class);
        
        $this->assertGreaterThan(0, \App\Models\User::count());
    }

    /**
     * @test
     */
    public function user_seeder_users_have_valid_emails()
    {
        $this->seed(UserSeeder::class);
        
        $users = \App\Models\User::all();
        
        foreach ($users as $user) {
            $this->assertNotNull($user->email);
            $this->assertTrue(filter_var($user->email, FILTER_VALIDATE_EMAIL) !== false);
        }
    }

    /**
     * @test
     */
    public function user_seeder_users_have_roles()
    {
        $this->seed(UserSeeder::class);
        
        $users = \App\Models\User::all();
        
        foreach ($users as $user) {
            $this->assertNotNull($user->role);
            $this->assertContains($user->role, ['user', 'admin', 'manager']);
        }
    }

    /**
     * @test
     */
    public function user_seeder_creates_admin_user()
    {
        $this->seed(UserSeeder::class);
        
        $adminExists = \App\Models\User::where('role', 'admin')->exists();
        
        $this->assertTrue($adminExists);
    }
}
