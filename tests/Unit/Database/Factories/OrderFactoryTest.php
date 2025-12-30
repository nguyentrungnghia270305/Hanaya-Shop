<?php

namespace Tests\Unit\Database\Factories;

use App\Models\Order\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFactoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function factory_creates_order_with_required_fields()
    {
        $order = Order::factory()->create();

        $this->assertNotNull($order->user_id);
        $this->assertNotNull($order->total_price);
        $this->assertNotNull($order->status);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
        ]);
    }

    /**
     * @test
     */
    public function factory_sets_default_status()
    {
        $order = Order::factory()->create();

        $this->assertContains($order->status, ['pending', 'processing', 'shipped', 'completed', 'cancelled']);
    }

    /**
     * @test
     */
    public function factory_creates_user_automatically()
    {
        $order = Order::factory()->create();

        $this->assertInstanceOf(User::class, $order->user);
    }

    /**
     * @test
     */
    public function factory_sets_total_price()
    {
        $order = Order::factory()->create();

        $this->assertIsNumeric($order->total_price);
        $this->assertGreaterThan(0, $order->total_price);
    }

    /**
     * @test
     */
    public function factory_can_override_attributes()
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total_price' => 5000.00,
            'status' => 'completed',
        ]);

        $this->assertEquals($user->id, $order->user_id);
        $this->assertEquals(5000.00, $order->total_price);
        $this->assertEquals('completed', $order->status);
    }

    /**
     * @test
     */
    public function factory_can_create_multiple_orders()
    {
        $orders = Order::factory()->count(5)->create();

        $this->assertCount(5, $orders);
        $this->assertEquals(5, Order::count());
    }
}
