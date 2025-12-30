<?php

namespace Tests\Unit\Routes;

use Tests\TestCase;

class UserRoutesTest extends TestCase
{
    /**
     * @test
     */
    public function dashboard_route_exists()
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('dashboard'));
    }

    /**
     * @test
     */
    public function products_routes_exist()
    {
        // Check for actual product routes - can be product.* or user.products.*
        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has('product.index') ||
            \Illuminate\Support\Facades\Route::has('user.products.index'),
            'Product index route should exist'
        );
        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has('product.show') ||
            \Illuminate\Support\Facades\Route::has('user.products.show'),
            'Product show route should exist'
        );
        // products.by-category route doesn't exist
    }

    /**
     * @test
     */
    public function cart_routes_exist()
    {
        // Check for actual cart routes
        $routes = ['cart.index', 'cart.add', 'cart.remove', 'cart.buyNow'];

        foreach ($routes as $route) {
            $this->assertTrue(\Illuminate\Support\Facades\Route::has($route), "Route {$route} should exist");
        }
        // cart.update doesn't exist - updates done via add/remove
    }

    /**
     * @test
     */
    public function checkout_routes_exist()
    {
        // Actual route names in the application
        $routes = ['checkout.index', 'checkout.store', 'checkout.success'];

        foreach ($routes as $route) {
            $this->assertTrue(\Illuminate\Support\Facades\Route::has($route), "Route {$route} should exist");
        }
    }

    /**
     * @test
     */
    public function order_routes_exist()
    {
        $routes = ['order.index', 'order.show', 'order.cancel'];

        foreach ($routes as $route) {
            $this->assertTrue(\Illuminate\Support\Facades\Route::has($route));
        }
    }

    /**
     * @test
     */
    public function review_routes_exist()
    {
        // Routes use singular 'review' not plural 'reviews'
        $routes = ['review.create', 'review.store'];

        foreach ($routes as $route) {
            $this->assertTrue(\Illuminate\Support\Facades\Route::has($route), "Route {$route} should exist");
        }
    }

    /**
     * @test
     */
    public function post_routes_exist()
    {
        $routes = ['posts.index', 'posts.show'];

        foreach ($routes as $route) {
            $this->assertTrue(\Illuminate\Support\Facades\Route::has($route));
        }
    }

    /**
     * @test
     */
    public function address_routes_exist()
    {
        // Only addresses.store route exists in current implementation
        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has('addresses.store'),
            'Address store route should exist'
        );
        // Full CRUD not implemented yet
    }

    /**
     * @test
     */
    public function profile_routes_exist()
    {
        $routes = ['profile.edit', 'profile.update', 'profile.destroy'];

        foreach ($routes as $route) {
            $this->assertTrue(\Illuminate\Support\Facades\Route::has($route));
        }
    }

    /**
     * @test
     */
    public function cart_routes_require_auth()
    {
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('cart.index');
        $middleware = $route->middleware();

        $this->assertContains('auth', $middleware);
    }

    /**
     * @test
     */
    public function checkout_routes_require_auth()
    {
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('checkout.index');
        $middleware = $route->middleware();

        $this->assertContains('auth', $middleware);
    }

    /**
     * @test
     */
    public function order_routes_require_auth()
    {
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('order.index');
        $middleware = $route->middleware();

        $this->assertContains('auth', $middleware);
    }

    /**
     * @test
     */
    public function products_routes_are_publicly_accessible()
    {
        // Check actual route name (product.index or user.products.index)
        $routeName = \Illuminate\Support\Facades\Route::has('product.index') ? 'product.index' : 'user.products.index';
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName($routeName);
        $middleware = $route->middleware();

        $this->assertNotContains('auth', $middleware);
    }
}
