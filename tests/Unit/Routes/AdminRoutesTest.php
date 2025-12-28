<?php

namespace Tests\Unit\Routes;

use Tests\TestCase;

class AdminRoutesTest extends TestCase
{
    /**
     * @test
     */
    public function admin_dashboard_route_exists()
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('admin.dashboard'));
    }

    /**
     * @test
     */
    public function admin_product_routes_exist()
    {
        $routes = ['admin.product', 'admin.product.create', 'admin.product.store', 
                   'admin.product.show', 'admin.product.edit', 'admin.product.update', 
                   'admin.product.destroy'];
        
        foreach ($routes as $route) {
            $this->assertTrue(\Illuminate\Support\Facades\Route::has($route), "Route {$route} does not exist");
        }
    }

    /**
     * @test
     */
    public function admin_category_routes_exist()
    {
        $routes = ['admin.category', 'admin.category.create', 'admin.category.store',
                   'admin.category.edit', 'admin.category.update', 'admin.category.show',
                   'admin.category.destroy'];
        
        foreach ($routes as $route) {
            $this->assertTrue(\Illuminate\Support\Facades\Route::has($route));
        }
    }

    /**
     * @test
     */
    public function admin_user_routes_exist()
    {
        $routes = ['admin.user', 'admin.user.create', 'admin.user.store',
                   'admin.user.edit', 'admin.user.update', 'admin.user.destroy'];
        
        foreach ($routes as $route) {
            $this->assertTrue(\Illuminate\Support\Facades\Route::has($route));
        }
    }

    /**
     * @test
     */
    public function admin_order_routes_exist()
    {
        $routes = ['admin.order', 'admin.order.show', 'admin.order.confirm'];

        foreach ($routes as $route) {
            $this->assertTrue(\Illuminate\Support\Facades\Route::has($route));
        }
    }    /**
     * @test
     */
    public function admin_post_routes_exist()
    {
        $routes = ['admin.post.index', 'admin.post.create', 'admin.post.store',
                   'admin.post.show', 'admin.post.edit', 'admin.post.update',
                   'admin.post.destroy'];
        
        foreach ($routes as $route) {
            $this->assertTrue(\Illuminate\Support\Facades\Route::has($route));
        }
    }

    /**
     * @test
     */
    public function admin_routes_have_auth_middleware()
    {
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('admin.dashboard');
        $middleware = $route->middleware();
        
        $this->assertContains('auth', $middleware);
    }

    /**
     * @test
     */
    public function admin_routes_have_admin_middleware()
    {
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('admin.dashboard');
        $middleware = $route->gatherMiddleware();
        
        $hasAdminMiddleware = false;
        foreach ($middleware as $m) {
            if (str_contains($m, 'IsAdmin') || $m === 'admin') {
                $hasAdminMiddleware = true;
                break;
            }
        }
        
        $this->assertTrue($hasAdminMiddleware);
    }

    /**
     * @test
     */
    public function admin_routes_use_admin_prefix()
    {
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('admin.dashboard');
        $uri = $route->uri();
        
        $this->assertStringStartsWith('admin/', $uri);
    }

    /**
     * @test
     */
    public function admin_upload_routes_exist()
    {
        $routes = ['admin.upload.ckeditor.image', 'admin.upload.post.image', 
                   'admin.upload.tinymce.image'];
        
        foreach ($routes as $route) {
            $this->assertTrue(\Illuminate\Support\Facades\Route::has($route));
        }
    }

    /**
     * @test
     */
    public function admin_routes_use_correct_http_methods()
    {
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('admin.product.store');
        $this->assertContains('POST', $route->methods());
        
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('admin.product.update');
        $this->assertContains('PUT', $route->methods());
        
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('admin.product.destroy');
        $this->assertContains('DELETE', $route->methods());
    }
}
