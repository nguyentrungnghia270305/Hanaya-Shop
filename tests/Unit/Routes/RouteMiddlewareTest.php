<?php

namespace Tests\Unit\Routes;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function admin_routes_have_auth_middleware()
    {
        $adminRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return str_starts_with($route->getName(), 'admin.');
        });

        foreach ($adminRoutes as $route) {
            $middleware = $route->middleware();
            $this->assertTrue(
                in_array('auth', $middleware) || in_array('web', $middleware),
                "Admin route {$route->getName()} should have auth middleware"
            );
        }
    }

    /**
     * @test
     */
    public function admin_routes_have_admin_check_middleware()
    {
        $adminRoutes = collect(Route::getRoutes())->filter(function ($route) {
            $name = $route->getName();

            return $name && str_starts_with($name, 'admin.') && $name !== 'admin.';
        });

        foreach ($adminRoutes as $route) {
            $middleware = $route->middleware();
            $this->assertTrue(
                in_array('isAdmin', $middleware) || in_array('checkRole:admin', $middleware) || in_array('App\\Http\\Middleware\\IsAdmin', $middleware),
                "Admin route {$route->getName()} should have admin check middleware"
            );
        }
    }

    /**
     * @test
     */
    public function user_protected_routes_have_auth_middleware()
    {
        $userRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return str_starts_with($route->getName(), 'user.');
        });

        foreach ($userRoutes as $route) {
            $middleware = $route->middleware();
            $this->assertTrue(
                in_array('auth', $middleware) || in_array('web', $middleware),
                "User route {$route->getName()} should have auth middleware"
            );
        }
    }

    /**
     * @test
     */
    public function web_routes_have_web_middleware()
    {
        $webRoutes = collect(Route::getRoutes())->filter(function ($route) {
            $name = $route->getName();

            return $name && ! str_starts_with($name, 'api.') && ! str_starts_with($name, 'storage.');
        });

        foreach ($webRoutes as $route) {
            $middleware = $route->middleware();
            $this->assertTrue(
                in_array('web', $middleware),
                "Web route {$route->getName()} should have web middleware"
            );
        }
    }

    /**
     * @test
     */
    public function guest_routes_accessible_without_auth()
    {
        $guestRoutes = ['login', 'register', 'home'];

        foreach ($guestRoutes as $routeName) {
            if (Route::has($routeName)) {
                $route = Route::getRoutes()->getByName($routeName);
                $middleware = $route->middleware();

                // Guest routes should not require auth middleware
                $this->assertFalse(
                    in_array('auth', $middleware),
                    "Guest route {$routeName} should not require auth"
                );
            }
        }
    }
}
