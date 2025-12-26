<?php

namespace Tests\Unit\App\Models\Order;

use App\Models\Order\Order;
use App\Models\Order\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function payment_can_be_created_with_required_fields()
    {
        $order = Order::factory()->create();

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'credit_card',
            'payment_status' => 'completed',
            'transaction_id' => 'TXN123456',
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => 'credit_card',
            'payment_status' => 'completed',
        ]);
    }

    /** @test */
    public function payment_belongs_to_order()
    {
        $order = Order::factory()->create();
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
        ]);

        $this->assertInstanceOf(Order::class, $payment->order);
        $this->assertEquals($order->id, $payment->order->id);
    }

    /** @test */
    public function payment_can_have_different_payment_methods()
    {
        $order = Order::factory()->create();

        $codPayment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
        ]);

        $this->assertEquals('cash_on_delivery', $codPayment->payment_method);
    }

    /** @test */
    public function payment_status_can_be_updated()
    {
        $payment = Payment::create([
            'order_id' => Order::factory()->create()->id,
            'payment_method' => 'credit_card',
            'payment_status' => 'pending',
        ]);

        $payment->update(['payment_status' => 'completed']);

        $this->assertEquals('completed', $payment->fresh()->payment_status);
    }

    /** @test */
    public function payment_can_have_transaction_id()
    {
        $payment = Payment::create([
            'order_id' => Order::factory()->create()->id,
            'payment_method' => 'credit_card',
            'payment_status' => 'completed',
            'transaction_id' => 'STRIPE_TXN_789',
        ]);

        $this->assertEquals('STRIPE_TXN_789', $payment->transaction_id);
    }

    /** @test */
    public function payment_has_fillable_attributes()
    {
        $order = Order::factory()->create();
        $data = [
            'order_id' => $order->id,
            'payment_method' => 'paypal',
            'payment_status' => 'pending',
            'transaction_id' => 'PP123',
        ];

        $payment = Payment::create($data);

        $this->assertEquals('paypal', $payment->payment_method);
        $this->assertEquals('pending', $payment->payment_status);
    }

    /** @test */
    public function order_can_have_payment()
    {
        $order = Order::factory()->create();
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'credit_card',
            'payment_status' => 'completed',
        ]);

        $this->assertNotNull($order->payment);
        $this->assertInstanceOf(Payment::class, $order->payment);
    }
}
