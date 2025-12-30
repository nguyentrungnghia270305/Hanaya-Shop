<?php

namespace Tests\Unit\Routes;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RouteRegistrationTest extends TestCase
{
    /**
     * @test
     */
    public function home_route_is_registered()
    {
        // Check for dashboard route which serves as homepage
        $this->assertTrue(
            Route::has('dashboard'),
            'Home/Dashboard route should be registered'
        );
    }

    /**
     * @test
     */
    public function login_route_is_registered()
    {
        $this->assertTrue(Route::has('login'));
    }

    /**
     * @test
     */
    public function register_route_is_registered()
    {
        $this->assertTrue(Route::has('register'));
    }

    /**
     * @test
     */
    public function admin_dashboard_route_is_registered()
    {
        $this->assertTrue(Route::has('admin.dashboard'));
    }

    /**
     * @test
     */
    public function user_profile_route_is_registered()
    {
        // Check for profile route variants
        $this->assertTrue(
            Route::has('user.profile') || Route::has('profile') || Route::has('profile.edit'),
            'User profile route should be registered'
        );
    }

    /**
     * @test
     */
    public function products_index_route_is_registered()
    {
        // Check for product routes
        $this->assertTrue(
            Route::has('products.index') || Route::has('product.index') || Route::has('products'),
            'Products index route should be registered'
        );
    }

    /**
     * @test
     */
    public function cart_index_route_is_registered()
    {
        $this->assertTrue(Route::has('cart.index'));
    }

    /**
     * @test
     */
    public function checkout_route_is_registered()
    {
        // Check for checkout.index route which is the actual route name
        $this->assertTrue(
            Route::has('checkout.index'),
            'Checkout route should be registered'
        );
    }
}
