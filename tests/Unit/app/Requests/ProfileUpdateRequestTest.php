<?php

namespace Tests\Unit\App\Requests;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ProfileUpdateRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function request_has_validation_rules()
    {
        $user = User::factory()->create();
        
        $request = ProfileUpdateRequest::create('/profile', 'PUT');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        $rules = $request->rules();
        
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('email', $rules);
    }

    /**
     * @test
     */
    public function name_is_required()
    {
        $user = User::factory()->create();
        
        $request = ProfileUpdateRequest::create('/profile', 'PUT');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        $validator = Validator::make(['email' => 'test@example.com'], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    /**
     * @test
     */
    public function email_is_required()
    {
        $user = User::factory()->create();
        
        $request = ProfileUpdateRequest::create('/profile', 'PUT');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        $validator = Validator::make(['name' => 'Test User'], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    /**
     * @test
     */
    public function email_must_be_valid_email_format()
    {
        $user = User::factory()->create();
        
        $request = ProfileUpdateRequest::create('/profile', 'PUT');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'invalid-email'
        ], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    /**
     * @test
     */
    public function email_must_be_unique_except_current_user()
    {
        $user1 = User::factory()->create(['email' => 'existing@example.com']);
        $user2 = User::factory()->create(['email' => 'current@example.com']);
        
        $request = ProfileUpdateRequest::create('/profile', 'PUT');
        $request->setUserResolver(function () use ($user2) {
            return $user2;
        });
        
        // Test with existing email (should fail)
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'existing@example.com'
        ], $request->rules());
        
        $this->assertTrue($validator->fails());
    }

    /**
     * @test
     */
    public function user_can_keep_their_own_email()
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        
        $request = ProfileUpdateRequest::create('/profile', 'PUT');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'user@example.com'
        ], $request->rules());
        
        $this->assertTrue($validator->passes());
    }

    /**
     * @test
     */
    public function email_is_converted_to_lowercase()
    {
        $user = User::factory()->create();
        
        $request = ProfileUpdateRequest::create('/profile', 'PUT');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        $rules = $request->rules();
        
        $this->assertContains('lowercase', $rules['email']);
    }

    /**
     * @test
     */
    public function name_has_max_length_validation()
    {
        $user = User::factory()->create();
        
        $request = ProfileUpdateRequest::create('/profile', 'PUT');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        $rules = $request->rules();
        
        $this->assertContains('max:255', $rules['name']);
    }
}
