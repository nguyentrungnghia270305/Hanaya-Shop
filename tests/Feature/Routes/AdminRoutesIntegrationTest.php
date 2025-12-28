<?php

namespace Tests\Feature\Routes;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoutesIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /** @test */
    public function admin_routes_require_authentication()
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function admin_routes_require_admin_role()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_dashboard()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertOk();
    }

    /** @test */
    public function admin_product_routes_are_accessible()
    {
        $routes = [
            'admin.product',
            'admin.product.create',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->admin)->get(route($route));
            $response->assertOk();
        }
    }

    /** @test */
    public function admin_category_routes_are_accessible()
    {
        $routes = [
            'admin.category',
            'admin.category.create',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->admin)->get(route($route));
            $response->assertOk();
        }
    }

    /** @test */
    public function admin_post_routes_are_accessible()
    {
        $routes = [
            'admin.post.index',
            'admin.post.create',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->admin)->get(route($route));
            $response->assertOk();
        }
    }

    /** @test */
    public function admin_user_management_routes_are_accessible()
    {
        $routes = [
            'admin.user',
            'admin.user.create',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->admin)->get(route($route));
            $response->assertOk();
        }
    }

    /** @test */
    public function admin_order_routes_are_accessible()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.order'));
        $response->assertOk();
    }

    /** @test */
    public function admin_root_redirects_to_dashboard()
    {
        $response = $this->actingAs($this->admin)->get('/admin');
        $response->assertRedirect('/admin/dashboard');
    }

    /** @test */
    public function admin_search_routes_work()
    {
        $searchRoutes = [
            'admin.product.search',
            'admin.category.search',
            'admin.user.search',
        ];

        foreach ($searchRoutes as $route) {
            $response = $this->actingAs($this->admin)->get(route($route, ['search' => 'test']));
            $response->assertOk();
        }
    }

    /** @test */
    public function admin_profile_route_accessible()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.profile.edit'));
        $response->assertOk();
    }
}
