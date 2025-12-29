<?php

namespace Tests\Unit\App\Middleware;

use App\Http\Middleware\CheckRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CheckRoleTest extends TestCase
{
    use RefreshDatabase;

    protected CheckRole $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new CheckRole();
    }

    /**
     * Test middleware allows user with matching role
     */
    public function test_allows_user_with_matching_role(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Auth::login($user);
        
        $request = Request::create('/', 'GET');
        $called = false;
        
        $response = $this->middleware->handle($request, function ($req) use (&$called) {
            $called = true;
            return response('OK');
        }, 'admin');
        
        $this->assertTrue($called);
        $this->assertEquals('OK', $response->getContent());
    }

    /**
     * Test middleware allows user with one of multiple roles
     */
    public function test_allows_user_with_one_of_multiple_roles(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Auth::login($user);
        
        $request = Request::create('/', 'GET');
        $called = false;
        
        $response = $this->middleware->handle($request, function ($req) use (&$called) {
            $called = true;
            return response('OK');
        }, 'admin', 'user');
        
        $this->assertTrue($called);
    }

    /**
     * Test middleware blocks user with non-matching role
     */
    public function test_blocks_user_with_non_matching_role(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Unauthorized access.');
        
        $user = User::factory()->create(['role' => 'user']);
        Auth::login($user);
        
        $request = Request::create('/', 'GET');
        
        $this->middleware->handle($request, function ($req) {
            return response('OK');
        }, 'admin');
    }

    /**
     * Test middleware blocks unauthenticated user
     */
    public function test_blocks_unauthenticated_user(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Unauthorized access.');
        
        Auth::logout();
        
        $request = Request::create('/', 'GET');
        
        $this->middleware->handle($request, function ($req) {
            return response('OK');
        }, 'admin');
    }

    /**
     * Test middleware checks multiple roles correctly
     */
    public function test_checks_multiple_roles_correctly(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Auth::login($user);
        
        $request = Request::create('/', 'GET');
        $called = false;
        
        $response = $this->middleware->handle($request, function ($req) use (&$called) {
            $called = true;
            return response('OK');
        }, 'admin', 'user', 'moderator');
        
        $this->assertTrue($called);
    }

    /**
     * Test middleware blocks when user role not in allowed roles
     */
    public function test_blocks_when_role_not_in_allowed_list(): void
    {
        $this->expectException(HttpException::class);
        
        $user = User::factory()->create(['role' => 'user']);
        Auth::login($user);
        
        $request = Request::create('/', 'GET');
        
        $this->middleware->handle($request, function ($req) {
            return response('OK');
        }, 'admin', 'moderator');
    }

    /**
     * Test middleware returns 403 status code on unauthorized access
     */
    public function test_returns_403_on_unauthorized_access(): void
    {
        try {
            $user = User::factory()->create(['role' => 'user']);
            Auth::login($user);
            
            $request = Request::create('/', 'GET');
            
            $this->middleware->handle($request, function ($req) {
                return response('OK');
            }, 'admin');
            
            $this->fail('Expected HttpException was not thrown');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
    }

    /**
     * Test middleware passes request to next middleware on success
     */
    public function test_passes_request_to_next_on_success(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        Auth::login($user);
        
        $request = Request::create('/', 'GET');
        $passedRequest = null;
        
        $this->middleware->handle($request, function ($req) use (&$passedRequest) {
            $passedRequest = $req;
            return response('OK');
        }, 'admin');
        
        $this->assertSame($request, $passedRequest);
    }
}
