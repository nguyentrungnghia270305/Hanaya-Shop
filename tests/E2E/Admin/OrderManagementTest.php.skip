<?php

namespace Tests\E2E\Admin;

use App\Models\Order\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /**
     * @test
     */
    public function admin_can_view_and_manage_orders()
    {
        // Create test orders
        $pendingOrder = Order::factory()->create([
            'status' => 'pending',
            'total_price' => 1000
        ]);
        $shippedOrder = Order::factory()->create([
            'status' => 'shipped',
            'total_price' => 2000
        ]);
        
        // View orders list
        $response = $this->actingAs($this->admin)
            ->get(route('admin.order'));
        
        $response->assertStatus(200);
        
        // View specific order details
        $response = $this->actingAs($this->admin)
            ->get(route('admin.order.show', $pendingOrder->id));
        
        $response->assertStatus(200);
        
        // Confirm order
        $response = $this->actingAs($this->admin)
            ->put(route('admin.order.confirm', $pendingOrder->id));
        
        $response->assertRedirect();
        
        // Mark as shipped
        $response = $this->actingAs($this->admin)
            ->put(route('admin.order.shipped', $pendingOrder->id));
        
        $response->assertRedirect();
        
        // Cancel an order
        $response = $this->actingAs($this->admin)
            ->put(route('admin.orders.cancel', $shippedOrder->id));
        
        $response->assertRedirect();
    }

    /**
     * @test
     */
    public function admin_can_view_order_statistics()
    {
        Order::factory()->count(10)->create([
            'status' => 'completed',
            'total_price' => 1000
        ]);
        
        $response = $this->actingAs($this->admin)
            ->get(route('admin.order'));
        
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
