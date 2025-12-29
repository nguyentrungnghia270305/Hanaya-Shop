<?php

namespace Tests\Unit\App\Models\Order;

use App\Models\Order\Order;
use App\Models\Order\OrderDetail;
use App\Models\Product\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function order_detail_can_be_created_with_required_fields()
    {
        $order = Order::factory()->create();
        $product = Product::factory()->create();

        $orderDetail = OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => 100.00,
        ]);

        $this->assertDatabaseHas('order_details', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => 100.00,
        ]);
    }

    /** @test */
    public function order_detail_belongs_to_order()
    {
        $order = Order::factory()->create();
        $orderDetail = OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => Product::factory()->create()->id,
            'quantity' => 1,
            'price' => 50.00,
        ]);

        $this->assertInstanceOf(Order::class, $orderDetail->order);
        $this->assertEquals($order->id, $orderDetail->order->id);
    }

    /** @test */
    public function order_detail_belongs_to_product()
    {
        $product = Product::factory()->create(['name' => 'Test Product']);
        $orderDetail = OrderDetail::create([
            'order_id' => Order::factory()->create()->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 75.00,
        ]);

        $this->assertInstanceOf(Product::class, $orderDetail->product);
        $this->assertEquals('Test Product', $orderDetail->product->name);
    }

    /** @test */
    public function order_detail_preserves_historical_price()
    {
        $product = Product::factory()->create(['price' => 100.00]);
        $orderDetail = OrderDetail::create([
            'order_id' => Order::factory()->create()->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 80.00,
        ]);

        $product->update(['price' => 120.00]);

        $this->assertEquals(80.00, $orderDetail->fresh()->price);
        $this->assertEquals(120.00, $product->fresh()->price);
    }

    /** @test */
    public function order_detail_has_fillable_attributes()
    {
        $data = [
            'order_id' => Order::factory()->create()->id,
            'product_id' => Product::factory()->create()->id,
            'quantity' => 5,
            'price' => 150.00,
        ];

        $orderDetail = OrderDetail::create($data);

        $this->assertEquals(5, $orderDetail->quantity);
        $this->assertEquals(150.00, $orderDetail->price);
    }

    /** @test */
    public function order_can_have_multiple_order_details()
    {
        $order = Order::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'price' => 50.00,
        ]);

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'price' => 100.00,
        ]);

        $this->assertCount(2, $order->orderDetail);
    }
}
