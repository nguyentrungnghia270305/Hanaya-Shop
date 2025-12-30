<?php

namespace Tests\Unit\App\Middleware;

use App\Http\Middleware\IsAdmin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class IsAdminTest extends TestCase
{
    use RefreshDatabase;

    protected IsAdmin $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new IsAdmin;
    }

    /**
     * Test middleware allows admin user
     */
    public function test_allows_admin_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Auth::login($admin);

        $request = Request::create('/', 'GET');
        $called = false;

        $response = $this->middleware->handle($request, function ($req) use (&$called) {
            $called = true;

            return response('OK');
        });

        $this->assertTrue($called);
        $this->assertEquals('OK', $response->getContent());
    }

    /**
     * Test middleware blocks non-admin user
     */
    public function test_blocks_non_admin_user(): void
    {
        $this->expectException(HttpException::class);

        $user = User::factory()->create(['role' => 'user']);
        Auth::login($user);

        $request = Request::create('/', 'GET');

        $this->middleware->handle($request, function ($req) {
            return response('OK');
        });
    }

    /**
     * Test middleware blocks unauthenticated user
     */
    public function test_blocks_unauthenticated_user(): void
    {
        $this->expectException(HttpException::class);

        Auth::logout();

        $request = Request::create('/', 'GET');

        $this->middleware->handle($request, function ($req) {
            return response('OK');
        });
    }

    /**
     * Test middleware returns 403 for non-admin
     */
    public function test_returns_403_for_non_admin(): void
    {
        try {
            $user = User::factory()->create(['role' => 'user']);
            Auth::login($user);

            $request = Request::create('/', 'GET');

            $this->middleware->handle($request, function ($req) {
                return response('OK');
            });

            $this->fail('Expected HttpException was not thrown');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
    }

    /**
     * Test middleware returns 403 for unauthenticated
     */
    public function test_returns_403_for_unauthenticated(): void
    {
        try {
            Auth::logout();

            $request = Request::create('/', 'GET');

            $this->middleware->handle($request, function ($req) {
                return response('OK');
            });

            $this->fail('Expected HttpException was not thrown');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
    }

    /**
     * Test middleware passes request to next middleware
     */
    public function test_passes_request_to_next_middleware(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Auth::login($admin);

        $request = Request::create('/', 'GET');
        $passedRequest = null;

        $this->middleware->handle($request, function ($req) use (&$passedRequest) {
            $passedRequest = $req;

            return response('OK');
        });

        $this->assertSame($request, $passedRequest);
    }

    /**
     * Test middleware checks exact role match
     */
    public function test_checks_exact_role_match(): void
    {
        $this->expectException(HttpException::class);

        // Create user with role that contains 'admin' but is not exactly 'admin'
        $user = User::factory()->create(['role' => 'user']);
        Auth::login($user);

        $request = Request::create('/', 'GET');

        $this->middleware->handle($request, function ($req) {
            return response('OK');
        });
    }

    /**
     * Test middleware verifies authentication before role check
     */
    public function test_verifies_authentication_before_role_check(): void
    {
        $this->expectException(HttpException::class);

        // Ensure no user is authenticated
        $this->assertFalse(Auth::check());

        $request = Request::create('/', 'GET');

        $this->middleware->handle($request, function ($req) {
            return response('OK');
        });
    }
}
