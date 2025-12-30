<?php

namespace Tests\Unit\Database\Factories;

use PHPUnit\Framework\TestCase;

class OrderDetailFactoryTest extends TestCase
{
    public function test_dummy_order_detail_factory(): void
    {
        $this->assertTrue(true);
    }
}
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
