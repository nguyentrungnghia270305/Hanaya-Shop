<?php

namespace Tests\Unit\Routes;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteParametersTest extends TestCase
{






    /**
     * @test
     */
    public function routes_with_parameters_use_consistent_naming()
    {
        $routes = Route::getRoutes();
        
        foreach ($routes as $route) {
            $uri = $route->uri();
            
            // Check if URI has parameters
            if (preg_match_all('/\{([^}]+)\}/', $uri, $matches)) {
                foreach ($matches[1] as $param) {
                    // Parameters should be lowercase/camelCase alphanumeric with optional underscores
                    $this->assertMatchesRegularExpression('/^[a-zA-Z][a-zA-Z0-9_]*$/', $param);
                }
            }
        }
        
        $this->assertTrue(true); // Test passes if no assertion failures
    }

    /**
     * @test
     */
    public function resource_routes_have_standard_parameters()
    {
        $resourceRoutes = collect(Route::getRoutes())->filter(function ($route) {
            $name = $route->getName();
            $uri = $route->uri();
            // Check if it's a resource route pattern (plural noun before .show/.edit)
            return $name && 
                   (str_contains($name, '.show') || str_contains($name, '.edit') ||
                    str_contains($name, '.update') || str_contains($name, '.destroy')) &&
                   preg_match('/\w+s\./', $name); // Plural resource names
        });
        
        foreach ($resourceRoutes as $route) {
            $uri = $route->uri();
            
            // Resource routes should have parameters
            $this->assertMatchesRegularExpression('/\{[^}]+\}/', $uri, 
                "Resource route {$route->getName()} should have a parameter");
        }
        
        $this->assertGreaterThan(0, $resourceRoutes->count());
    }
}
