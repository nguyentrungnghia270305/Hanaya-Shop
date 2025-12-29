<?php

namespace Tests\Unit\App\Services;

use App\Models\Order\Order;
use App\Models\Order\Payment;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PaymentService();
    }

    /**
     * @test
     */
    public function service_can_be_instantiated()
    {
        $this->assertInstanceOf(PaymentService::class, $this->service);
    }

    /**
     * @test
     */
    public function can_process_credit_card_payment()
    {
        $order = Order::factory()->create();
        
        $result = $this->service->processPayment('credit_card', $order, [
            'last_digits' => '1234'
        ]);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('transaction_id', $result);
        $this->assertStringStartsWith('CC_', $result['transaction_id']);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => 'credit_card',
            'payment_status' => 'completed'
        ]);
    }

    /**
     * @test
     */
    public function can_process_paypal_payment()
    {
        $order = Order::factory()->create();
        
        $result = $this->service->processPayment('paypal', $order);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('transaction_id', $result);
        $this->assertStringStartsWith('PP_', $result['transaction_id']);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => 'paypal',
            'payment_status' => 'completed'
        ]);
    }

    /**
     * @test
     */
    public function can_process_cash_on_delivery_payment()
    {
        $order = Order::factory()->create();
        
        $result = $this->service->processPayment('cash_on_delivery', $order);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('transaction_id', $result);
        $this->assertEquals('COD_' . $order->id, $result['transaction_id']);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending'
        ]);
    }

    /**
     * @test
     */
    public function rejects_invalid_payment_method()
    {
        $order = Order::factory()->create();
        
        $result = $this->service->processPayment('bitcoin', $order);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }

    /**
     * @test
     */
    public function updates_order_status_after_payment()
    {
        $order = Order::factory()->create(['status' => 'pending']);
        
        $this->service->processPayment('credit_card', $order, [
            'last_digits' => '1234'
        ]);
        
        $this->assertEquals('pending', $order->fresh()->status);
    }

    /**
     * @test
     */
    public function can_update_payment_status()
    {
        $order = Order::factory()->create();
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'credit_card',
            'payment_status' => 'pending'
        ]);
        
        $result = $this->service->updatePaymentStatus($payment, 'completed');
        
        $this->assertTrue($result);
        $this->assertEquals('completed', $payment->fresh()->payment_status);
    }

    /**
     * @test
     */
    public function rejects_invalid_payment_status()
    {
        $order = Order::factory()->create();
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'credit_card',
            'payment_status' => 'pending'
        ]);
        
        $result = $this->service->updatePaymentStatus($payment, 'invalid_status');
        
        $this->assertFalse($result);
        $this->assertEquals('pending', $payment->fresh()->payment_status);
    }

    /**
     * @test
     */
    public function credit_card_payment_requires_card_info()
    {
        $order = Order::factory()->create();
        
        $result = $this->service->processPayment('credit_card', $order, []);
        
        $this->assertFalse($result['success']);
    }

    /**
     * @test
     */
    public function payment_creates_transaction_id()
    {
        $order = Order::factory()->create();
        
        $result = $this->service->processPayment('paypal', $order);
        
        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertNotNull($payment->transaction_id);
        $this->assertEquals($result['transaction_id'], $payment->transaction_id);
    }
}
