<?php

namespace Tests\E2E\User;

use App\Models\Order\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * @test
     */
    public function user_can_view_order_history()
    {
        $this->actingAs($this->user);

        // Create multiple orders
        Order::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
        ]);

        Order::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->get(route('order.index'));

        $response->assertStatus(200);
        // Verify orders are displayed
    }

    /**
     * @test
     */
    public function guest_cannot_access_order_tracking()
    {
        $order = Order::factory()->create();

        $response = $this->get(route('order.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('order.show', $order->id));
        $response->assertRedirect(route('login'));
    }
}
