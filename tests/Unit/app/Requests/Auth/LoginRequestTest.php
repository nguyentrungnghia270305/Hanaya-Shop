<?php

namespace Tests\Unit\App\Requests\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LoginRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function request_is_always_authorized()
    {
        $request = new LoginRequest;

        $this->assertTrue($request->authorize());
    }

    /**
     * @test
     */
    public function request_has_validation_rules()
    {
        $request = new LoginRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
    }

    /**
     * @test
     */
    public function email_is_required()
    {
        $request = new LoginRequest;
        $validator = Validator::make(['password' => 'password'], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    /**
     * @test
     */
    public function password_is_required()
    {
        $request = new LoginRequest;
        $validator = Validator::make(['email' => 'test@example.com'], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    /**
     * @test
     */
    public function email_must_be_valid_email_format()
    {
        $request = new LoginRequest;
        $validator = Validator::make([
            'email' => 'invalid-email',
            'password' => 'password',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    /**
     * @test
     */
    public function authenticate_method_logs_in_valid_user()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $request->authenticate();

        $this->assertAuthenticatedAs($user);
    }

    /**
     * @test
     */
    public function authenticate_method_throws_exception_for_invalid_credentials()
    {
        $this->expectException(ValidationException::class);

        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $request->authenticate();
    }

    /**
     * @test
     */
    public function authenticate_method_supports_remember_me()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => true,
        ]);

        $request->authenticate();

        $this->assertAuthenticatedAs($user);
    }

    /**
     * @test
     */
    public function request_has_rate_limiting()
    {
        RateLimiter::clear('test-throttle-key');

        $request = new LoginRequest;

        $this->assertTrue(method_exists($request, 'ensureIsNotRateLimited'));
        $this->assertTrue(method_exists($request, 'throttleKey'));
    }

    /**
     * @test
     */
    public function valid_credentials_pass_validation()
    {
        $request = new LoginRequest;
        $validator = Validator::make([
            'email' => 'test@example.com',
            'password' => 'password123',
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }
}
