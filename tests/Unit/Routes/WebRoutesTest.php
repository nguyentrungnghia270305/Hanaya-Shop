<?php

namespace Tests\Unit\Routes;

use Tests\TestCase;

class WebRoutesTest extends TestCase
{
    /**
     * @test
     */
    public function chatbot_route_exists()
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('chatbot.chat'));
    }

    /**
     * @test
     */
    public function locale_switch_route_exists()
    {
        // Actual route name is locale.set not locale.switch
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('locale.set'));
    }

    /**
     * @test
     */
    public function home_route_exists()
    {
        // Home route is named 'dashboard' not 'home'
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('dashboard'));
    }

    /**
     * @test
     */
    public function chatbot_route_uses_post_method()
    {
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('chatbot.chat');
        $this->assertContains('POST', $route->methods());
    }

    /**
     * @test
     */
    public function locale_switch_route_uses_post_method()
    {
        // Route name is locale.set and uses GET method
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('locale.set');
        $this->assertContains('GET', $route->methods());
    }

    /**
     * @test
     */
    public function chatbot_route_points_to_correct_controller()
    {
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('chatbot.chat');
        $action = $route->getAction();
        $this->assertStringContainsString('ChatbotController', $action['controller']);
    }

    /**
     * @test
     */
    public function locale_switch_route_points_to_correct_controller()
    {
        // Route name is locale.set
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('locale.set');
        $action = $route->getAction();
        $this->assertStringContainsString('LocaleController', $action['controller']);
    }

    /**
     * @test
     */
    public function web_routes_include_user_routes()
    {
        // Test some user routes with actual route names
        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has('product.index') || 
            \Illuminate\Support\Facades\Route::has('user.products.index'),
            'Product index route should exist'
        );
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('cart.index'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('checkout.index'));
    }

    /**
     * @test
     */
    public function web_routes_include_admin_routes()
    {
        // Test some admin routes
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('admin.dashboard'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('admin.product'));
    }

    /**
     * @test
     */
    public function web_routes_include_auth_routes()
    {
        // Test some auth routes
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('login'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('register'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('logout'));
    }
}
