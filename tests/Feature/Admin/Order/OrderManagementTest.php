<?php

namespace Tests\Feature\Admin\Order;

use App\Models\Order\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
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
    public function admin_can_view_all_orders()
    {
        Order::factory()->count(10)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.order'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.orders.index');
    }

    /**
     * @test
     */
    public function admin_can_view_order_details()
    {
        $order = Order::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.order.show', $order));

        $response->assertStatus(200);
        $response->assertViewIs('admin.orders.show');
    }

    /**
     * @test
     */
    public function admin_can_filter_orders_by_status()
    {
        Order::factory()->create(['status' => 'pending']);
        Order::factory()->create(['status' => 'completed']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.order', ['status' => 'pending']));

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function admin_can_search_orders()
    {
        $order = Order::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.order', ['search' => $order->id]));

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function non_admin_cannot_access_order_management()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)
            ->get(route('admin.order'));

        $response->assertStatus(403);
    }
}
