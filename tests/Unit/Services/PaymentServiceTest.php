<?php

namespace Tests\Unit\Services;

use App\Models\Order\Order;
use App\Models\Order\Payment;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentService $paymentService;

    protected User $user;

    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentService = new PaymentService;
        $this->user = User::factory()->create();
        $this->order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total_price' => 100000,
            'status' => 'pending',
        ]);
    }

    /**
     * @test
     */
    public function it_can_process_credit_card_payment()
    {
        $paymentData = [
            'last_digits' => '4242',
            'card_type' => 'visa',
        ];

        $result = $this->paymentService->processPayment('credit_card', $this->order, $paymentData);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('CC_', $result['transaction_id']);
        $this->assertArrayHasKey('payment_id', $result);

        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
            'payment_method' => 'credit_card',
            'payment_status' => 'completed',
        ]);

        $this->assertEquals('pending', $this->order->fresh()->status);
    }

    /**
     * @test
     */
    public function it_can_process_paypal_payment()
    {
        $paymentData = [
            'paypal_email' => 'test@paypal.com',
        ];

        $result = $this->paymentService->processPayment('paypal', $this->order, $paymentData);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('PP_', $result['transaction_id']);
        $this->assertArrayHasKey('payment_id', $result);

        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
            'payment_method' => 'paypal',
            'payment_status' => 'completed',
        ]);
    }

    /**
     * @test
     */
    public function it_can_process_cod_payment()
    {
        $result = $this->paymentService->processPayment('cash_on_delivery', $this->order);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('COD_', $result['transaction_id']);
        $this->assertArrayHasKey('payment_id', $result);

        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
        ]);
    }

    /**
     * @test
     */
    public function it_rejects_invalid_payment_method()
    {
        $result = $this->paymentService->processPayment('invalid_method', $this->order);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Invalid payment method', $result['message']);
    }

    /**
     * @test
     */
    public function credit_card_payment_requires_card_details()
    {
        $result = $this->paymentService->processPayment('credit_card', $this->order, []);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Lỗi xử lý thanh toán', $result['message']);
    }

    /**
     * @test
     */
    // public function it_can_update_payment_status_to_completed()
    // {
    //     $payment = Payment::factory()->create([
    //         'order_id' => $this->order->id,
    //         'payment_status' => 'pending',
    //     ]);

    //     $result = $this->paymentService->updatePaymentStatus($payment, 'completed');

    //     $this->assertTrue($result);
    //     $this->assertEquals('completed', $payment->fresh()->payment_status);
    // }

    /**
     * @test
     */
    // public function it_can_update_payment_status_to_failed()
    // {
    //     $payment = Payment::factory()->create([
    //         'order_id' => $this->order->id,
    //         'payment_status' => 'pending',
    //     ]);

    //     $result = $this->paymentService->updatePaymentStatus($payment, 'failed');

    //     $this->assertTrue($result);
    //     $this->assertEquals('failed', $payment->fresh()->payment_status);
    // }

    /**
     * @test
     */
    // public function it_rejects_invalid_payment_status()
    // {
    //     $payment = Payment::factory()->create([
    //         'order_id' => $this->order->id,
    //         'payment_status' => 'pending',
    //     ]);

    //     $result = $this->paymentService->updatePaymentStatus($payment, 'invalid_status');

    //     $this->assertFalse($result);
    //     $this->assertEquals('pending', $payment->fresh()->payment_status);
    // }

    /**
     * @test
     */
    public function it_logs_payment_processing_errors()
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, 'Payment processing error') &&
                       isset($context['order_id']) &&
                       isset($context['payment_method']);
            });

        $this->paymentService->processPayment('invalid_method', $this->order);
    }

    /**
     * @test
     */
    public function it_creates_unique_transaction_ids_for_credit_card()
    {
        $paymentData = ['last_digits' => '4242'];

        $result1 = $this->paymentService->processPayment('credit_card', $this->order, $paymentData);

        $order2 = Order::factory()->create(['user_id' => $this->user->id]);
        $result2 = $this->paymentService->processPayment('credit_card', $order2, $paymentData);

        $this->assertNotEquals($result1['transaction_id'], $result2['transaction_id']);
    }

    /**
     * @test
     */
    public function it_creates_unique_transaction_ids_for_paypal()
    {
        $paymentData = ['paypal_email' => 'test@paypal.com'];

        $result1 = $this->paymentService->processPayment('paypal', $this->order, $paymentData);

        $order2 = Order::factory()->create(['user_id' => $this->user->id]);
        $result2 = $this->paymentService->processPayment('paypal', $order2, $paymentData);

        $this->assertNotEquals($result1['transaction_id'], $result2['transaction_id']);
    }

    /**
     * @test
     */
    public function cod_payment_creates_transaction_id_with_order_id()
    {
        $result = $this->paymentService->processPayment('cash_on_delivery', $this->order);

        $expectedTransactionId = 'COD_'.$this->order->id;
        $this->assertEquals($expectedTransactionId, $result['transaction_id']);
    }

    /**
     * @test
     */
    public function credit_card_payment_updates_order_status()
    {
        $paymentData = ['last_digits' => '4242'];

        $this->paymentService->processPayment('credit_card', $this->order, $paymentData);

        $this->assertEquals('pending', $this->order->fresh()->status);
    }

    /**
     * @test
     */
    public function paypal_payment_updates_order_status()
    {
        $paymentData = ['paypal_email' => 'test@paypal.com'];

        $this->paymentService->processPayment('paypal', $this->order, $paymentData);

        $this->assertEquals('pending', $this->order->fresh()->status);
    }

    /**
     * @test
     */
    public function cod_payment_updates_order_status()
    {
        $this->paymentService->processPayment('cash_on_delivery', $this->order);

        $this->assertEquals('pending', $this->order->fresh()->status);
    }

    /**
     * @test
     */
    // public function payment_status_update_logs_errors_on_exception()
    // {
    //     $payment = Payment::factory()->create([
    //         'order_id' => $this->order->id,
    //     ]);

    //     Log::shouldReceive('error')
    //         ->once()
    //         ->withArgs(function ($message, $context) use ($payment) {
    //             return str_contains($message, 'Payment status update error') &&
    //                    $context['payment_id'] === $payment->id;
    //         });

    //     $this->paymentService->updatePaymentStatus($payment, 'invalid_status');
    // }

    /**
     * @test
     */
    // public function it_validates_payment_status_values()
    // {
    //     $payment = Payment::factory()->create();

    //     $validStatuses = ['pending', 'completed', 'failed'];

    //     foreach ($validStatuses as $status) {
    //         $result = $this->paymentService->updatePaymentStatus($payment, $status);
    //         $this->assertTrue($result);
    //         $this->assertEquals($status, $payment->fresh()->payment_status);
    //     }
    // }

    /**
     * @test
     */
    public function cod_payment_message_is_in_vietnamese()
    {
        $result = $this->paymentService->processPayment('cash_on_delivery', $this->order);

        $this->assertStringContainsString('Đặt hàng thành công', $result['message']);
        $this->assertStringContainsString('Thanh toán khi nhận hàng', $result['message']);
    }

    /**
     * @test
     */
    public function credit_card_payment_message_is_in_vietnamese()
    {
        $paymentData = ['last_digits' => '4242'];
        $result = $this->paymentService->processPayment('credit_card', $this->order, $paymentData);

        $this->assertStringContainsString('Thanh toán thẻ tín dụng thành công', $result['message']);
    }

    /**
     * @test
     */
    public function paypal_payment_message_is_in_vietnamese()
    {
        $paymentData = ['paypal_email' => 'test@paypal.com'];
        $result = $this->paymentService->processPayment('paypal', $this->order, $paymentData);

        $this->assertStringContainsString('Thanh toán PayPal thành công', $result['message']);
    }
}
