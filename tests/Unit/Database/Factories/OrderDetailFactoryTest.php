<?php

// namespace Tests\Unit\Database\Factories;

// use App\Models\Order\Order;
// use App\Models\Order\OrderDetail;
// use App\Models\Product\Product;
// use Database\Factories\Order\OrderDetailFactory;
// use Illuminate\Foundation\Testing\RefreshDatabase;
// use Tests\TestCase;

// class OrderDetailFactoryTest extends TestCase
// {
//     use RefreshDatabase;

//     /**
//      * @test
//      */
//     public function order_detail_factory_creates_order_detail()
//     {
//         $orderDetail = OrderDetail::factory()->create();

//         $this->assertInstanceOf(OrderDetail::class, $orderDetail);
//         $this->assertDatabaseHas('order_details', ['id' => $orderDetail->id]);
//     }

//     /**
//      * @test
//      */
//     public function order_detail_factory_sets_required_attributes()
//     {
//         $orderDetail = OrderDetail::factory()->create();

//         $this->assertNotNull($orderDetail->order_id);
//         $this->assertNotNull($orderDetail->product_id);
//         $this->assertNotNull($orderDetail->quantity);
//         $this->assertNotNull($orderDetail->price);
//     }

//     /**
//      * @test
//      */
//     public function order_detail_factory_can_override_attributes()
//     {
//         $order = Order::factory()->create();
//         $product = Product::factory()->create();
        
//         $orderDetail = OrderDetail::factory()->create([
//             'order_id' => $order->id,
//             'product_id' => $product->id,
//             'quantity' => 5,
//             'price' => 150.00
//         ]);

//         $this->assertEquals($order->id, $orderDetail->order_id);
//         $this->assertEquals($product->id, $orderDetail->product_id);
//         $this->assertEquals(5, $orderDetail->quantity);
//         $this->assertEquals(150.00, $orderDetail->price);
//     }

//     /**
//      * @test
//      */
//     public function order_detail_factory_sets_positive_quantity()
//     {
//         $orderDetail = OrderDetail::factory()->create();

//         $this->assertGreaterThan(0, $orderDetail->quantity);
//     }

//     /**
//      * @test
//      */
//     public function order_detail_factory_sets_positive_price()
//     {
//         $orderDetail = OrderDetail::factory()->create();

//         $this->assertGreaterThan(0, $orderDetail->price);
//     }

//     /**
//      * @test
//      */
//     public function order_detail_factory_can_create_multiple_details()
//     {
//         $order = Order::factory()->create();
//         $orderDetails = OrderDetail::factory()->count(3)->create(['order_id' => $order->id]);

//         $this->assertCount(3, $orderDetails);
//         foreach ($orderDetails as $detail) {
//             $this->assertEquals($order->id, $detail->order_id);
//         }
//     }

//     /**
//      * @test
//      */
//     public function order_detail_factory_calculates_subtotal_correctly()
//     {
//         $orderDetail = OrderDetail::factory()->create([
//             'quantity' => 3,
//             'price' => 100.00
//         ]);

//         if (isset($orderDetail->subtotal)) {
//             $this->assertEquals(300.00, $orderDetail->subtotal);
//         }
//     }
// }
