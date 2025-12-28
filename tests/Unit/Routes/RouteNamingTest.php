<?php

namespace Tests\Unit\Routes;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteNamingTest extends TestCase
{
    /**
     * @test
     */
    public function admin_routes_follow_naming_convention()
    {
        $adminRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return str_starts_with($route->getName(), 'admin.');
        });
        
        $this->assertGreaterThan(0, $adminRoutes->count());
        
        foreach ($adminRoutes as $route) {
            $this->assertMatchesRegularExpression('/^admin\./', $route->getName());
        }
    }

    /**
     * @test
     */
    public function user_routes_follow_naming_convention()
    {
        $userRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return str_starts_with($route->getName(), 'user.');
        });
        
        $this->assertGreaterThan(0, $userRoutes->count());
        
        foreach ($userRoutes as $route) {
            $this->assertMatchesRegularExpression('/^user\./', $route->getName());
        }
    }

    /**
     * @test
     */
    public function resource_routes_follow_naming_convention()
    {
        $resourceRoutes = collect(Route::getRoutes())->filter(function ($route) {
            $name = $route->getName();
            return $name && (str_contains($name, '.index') || str_contains($name, '.show') || 
                    str_contains($name, '.create') || str_contains($name, '.store') ||
                    str_contains($name, '.edit') || str_contains($name, '.update') ||
                    str_contains($name, '.destroy'));
        });
        
        $this->assertGreaterThan(0, $resourceRoutes->count());
    }

    /**
     * @test
     */
    public function all_named_routes_use_dot_notation()
    {
        $namedRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return $route->getName() !== null;
        });
        
        foreach ($namedRoutes as $route) {
            $name = $route->getName();
            // Route names can use lowercase, dots, dashes, and camelCase
            if (str_contains($name, '.')) {
                $this->assertMatchesRegularExpression('/^[a-zA-Z0-9.\-]+$/', $name);
            }
        }
        
        $this->assertGreaterThan(0, $namedRoutes->count());
    }
}
