<?php

namespace Tests\Unit\Routes;

use Tests\TestCase;

class AuthRoutesTest extends TestCase
{
    /**
     * @test
     */
    public function login_route_exists()
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('login'));
    }

    /**
     * @test
     */
    public function register_route_exists()
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('register'));
    }

    /**
     * @test
     */
    public function logout_route_exists()
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('logout'));
    }

    /**
     * @test
     */
    public function password_request_route_exists()
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('password.request'));
    }

    /**
     * @test
     */
    public function password_email_route_exists()
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('password.email'));
    }

    /**
     * @test
     */
    public function password_reset_route_exists()
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('password.reset'));
    }

    /**
     * @test
     */
    public function password_store_route_exists()
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('password.store'));
    }

    /**
     * @test
     */
    public function verification_notice_route_exists()
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('verification.notice'));
    }

    /**
     * @test
     */
    public function verification_verify_route_exists()
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('verification.verify'));
    }

    /**
     * @test
     */
    public function login_route_uses_get_method()
    {
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('login');
        $this->assertContains('GET', $route->methods());
    }

    /**
     * @test
     */
    public function login_post_uses_post_method()
    {
        $routes = \Illuminate\Support\Facades\Route::getRoutes();
        // Laravel Breeze has separate routes for login GET and POST
        // Check if there's a POST route to /login even if named differently
        $loginRoutes = collect($routes)->filter(function ($route) {
            return str_contains($route->uri(), 'login') && in_array('POST', $route->methods());
        });

        $this->assertGreaterThan(0, $loginRoutes->count());
    }    /**
     * @test
     */
    public function logout_route_uses_post_method()
    {
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('logout');
        $this->assertContains('POST', $route->methods());
    }

    /**
     * @test
     */
    public function register_route_uses_get_method()
    {
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('register');
        $this->assertContains('GET', $route->methods());
    }

    /**
     * @test
     */
    public function register_post_uses_post_method()
    {
        $routes = \Illuminate\Support\Facades\Route::getRoutes();
        // Laravel Breeze has separate routes for register GET and POST
        // Check if there's a POST route to /register even if named differently
        $registerRoutes = collect($routes)->filter(function ($route) {
            return str_contains($route->uri(), 'register') && in_array('POST', $route->methods());
        });

        $this->assertGreaterThan(0, $registerRoutes->count());
    }    /**
     * @test
     */
    public function password_update_route_exists()
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('password.update'));
    }

    /**
     * @test
     */
    public function confirm_password_route_exists()
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('password.confirm'));
    }
}
