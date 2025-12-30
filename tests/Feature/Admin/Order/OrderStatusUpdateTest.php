<?php

namespace Tests\Feature\Admin\Order;

use App\Models\Order\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        Notification::fake();
    }

    /**
     * @test
     */
    public function admin_can_update_order_status_to_confirmed()
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.order.confirm', $order), [
                'status' => 'processing',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
        ]);
    }

    /**
     * @test
     */
    public function admin_can_update_order_status_to_shipping()
    {
        $order = Order::factory()->create(['status' => 'processing']);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.order.shipped', $order), [
                'status' => 'shipped',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped',
        ]);
    }

    /**
     * @test
     */
    public function admin_can_cancel_order()
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.orders.cancel', $order), [
                'status' => 'cancelled',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
    }
}
