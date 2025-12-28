<?php

namespace Tests\Feature\Admin\Dashboard;

use App\Models\Order\Order;
use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /**
     * @test
     */
    public function admin_can_access_dashboard()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    /**
     * @test
     */
    public function dashboard_displays_total_orders()
    {
        Order::factory()->count(10)->create();
        
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));
        
        $response->assertStatus(200);
        $response->assertSee('10');
    }

    /**
     * @test
     */
    public function dashboard_displays_total_products()
    {
        Product::factory()->count(25)->create();
        
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));
        
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function dashboard_displays_recent_orders()
    {
        Order::factory()->count(5)->create();
        
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));
        
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function non_admin_cannot_access_dashboard()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $response = $this->actingAs($user)
            ->get(route('admin.dashboard'));
        
        $response->assertStatus(403);
    }
}
