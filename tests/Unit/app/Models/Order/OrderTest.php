<?php

namespace Tests\Unit\App\Models\Order;

use App\Models\Address;
use App\Models\Order\Order;
use App\Models\Order\OrderDetail;
use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function order_can_be_created_with_required_fields()
    {
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total_price' => 250.00,
            'status' => 'pending',
            'address_id' => $address->id,
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_price' => 250.00,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function order_belongs_to_user()
    {
        $user = User::factory()->create(['name' => 'Customer Name']);
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals('Customer Name', $order->user->name);
    }

    /** @test */
    public function order_belongs_to_address()
    {
        $address = Address::factory()->create();
        $order = Order::factory()->create(['address_id' => $address->id]);

        $this->assertInstanceOf(Address::class, $order->address);
    }

    /** @test */
    public function order_has_order_details_relationship()
    {
        $order = Order::factory()->create();
        $product = Product::factory()->create();

        // Create order details manually since factory doesn't exist
        $orderDetail1 = OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 100.00,
        ]);

        $product2 = Product::factory()->create();
        $orderDetail2 = OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'price' => 50.00,
        ]);

        $this->assertCount(2, $order->orderDetail);
        $this->assertInstanceOf(OrderDetail::class, $order->orderDetail->first());
    }

    /** @test */
    public function order_total_amount_accessor_returns_total_price()
    {
        $order = Order::factory()->create(['total_price' => 500.00]);

        $this->assertEquals(500.00, $order->total_amount);
    }

    /** @test */
    public function order_total_amount_mutator_sets_total_price()
    {
        $order = Order::factory()->create();

        $order->total_amount = 750.00;
        $order->save();

        $this->assertEquals(750.00, $order->total_price);
        $this->assertEquals(750.00, $order->fresh()->total_price);
    }

    /** @test */
    public function order_status_can_be_updated()
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $order->update(['status' => 'processing']);

        $this->assertEquals('processing', $order->fresh()->status);
    }

    /** @test */
    public function order_can_have_customer_message()
    {
        $order = Order::factory()->create(['message' => 'Please deliver before 5 PM']);

        $this->assertEquals('Please deliver before 5 PM', $order->message);
    }

    /** @test */
    public function order_timestamps_are_managed_automatically()
    {
        $order = Order::factory()->create();

        $this->assertNotNull($order->created_at);
        $this->assertNotNull($order->updated_at);
    }

    /** @test */
    public function user_can_have_multiple_orders()
    {
        $user = User::factory()->create();
        Order::factory()->count(5)->create(['user_id' => $user->id]);

        $this->assertCount(5, $user->order);
    }
}
