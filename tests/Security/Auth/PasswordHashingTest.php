<?php

namespace Tests\Security\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordHashingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function passwords_are_hashed_using_bcrypt()
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);
        
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertNotEquals('password123', $user->password);
    }

    /**
     * @test
     */
    public function password_hash_length_is_secure()
    {
        $hashedPassword = bcrypt('testpassword');
        
        // Bcrypt hashes are 60 characters long
        $this->assertEquals(60, strlen($hashedPassword));
    }

    /**
     * @test
     */
    public function same_password_generates_different_hashes()
    {
        $password = 'samepassword123';
        $hash1 = bcrypt($password);
        $hash2 = bcrypt($password);
        
        // Each hash should be unique due to salt
        $this->assertNotEquals($hash1, $hash2);
        
        // But both should verify correctly
        $this->assertTrue(Hash::check($password, $hash1));
        $this->assertTrue(Hash::check($password, $hash2));
    }

    /**
     * @test
     */
    public function hash_verification_fails_for_wrong_password()
    {
        $user = User::factory()->create(['password' => bcrypt('correctpassword')]);
        
        $this->assertFalse(Hash::check('wrongpassword', $user->password));
    }

    /**
     * @test
     */
    public function user_factory_automatically_hashes_passwords()
    {
        $user = User::factory()->create();
        
        // Factory should create hashed password
        $this->assertNotNull($user->password);
        $this->assertGreaterThan(50, strlen($user->password));
        
        // Should be able to verify with default factory password
        $this->assertTrue(Hash::check('password', $user->password));
    }

    /**
     * @test
     */
    public function password_hashing_is_one_way()
    {
        $password = 'originalpassword';
        $hash = bcrypt($password);
        
        // Cannot reverse engineer original password from hash
        $this->assertNotEquals($password, $hash);
        $this->assertStringNotContainsString($password, $hash);
    }
}
