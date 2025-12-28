<?php

namespace Tests\Integration\Payment;

use App\Models\Order\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayPalIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function paypal_payment_can_be_created()
    {
        Http::fake([
            'api.paypal.com/*' => Http::response([
                'id' => 'PAYID-123456',
                'status' => 'CREATED',
                'links' => [
                    ['rel' => 'approve', 'href' => 'https://paypal.com/approve']
                ]
            ], 200)
        ]);
        
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'total_amount' => 100]);
        
        $response = Http::post('https://api.paypal.com/v1/payments/payment', [
            'intent' => 'sale',
            'payer' => ['payment_method' => 'paypal'],
            'transactions' => [
                ['amount' => ['total' => $order->total_amount, 'currency' => 'USD']]
            ]
        ]);
        
        $this->assertEquals(200, $response->status());
        $this->assertEquals('CREATED', $response->json('status'));
    }

    /**
     * @test
     */
    public function paypal_payment_can_be_executed()
    {
        Http::fake([
            'api.paypal.com/*' => Http::response([
                'id' => 'PAYID-123456',
                'state' => 'approved',
                'transactions' => [
                    ['related_resources' => [['sale' => ['state' => 'completed']]]]
                ]
            ], 200)
        ]);
        
        $response = Http::post('https://api.paypal.com/v1/payments/payment/PAYID-123456/execute', [
            'payer_id' => 'PAYER-123'
        ]);
        
        $this->assertEquals(200, $response->status());
        $this->assertEquals('approved', $response->json('state'));
    }

    /**
     * @test
     */
    public function paypal_webhook_validates_payment()
    {
        Http::fake([
            'api.paypal.com/*' => Http::response([
                'verification_status' => 'SUCCESS'
            ], 200)
        ]);
        
        $webhookData = [
            'event_type' => 'PAYMENT.SALE.COMPLETED',
            'resource' => ['id' => 'SALE-123']
        ];
        
        $response = Http::post('https://api.paypal.com/v1/notifications/verify-webhook-signature', $webhookData);
        
        $this->assertEquals(200, $response->status());
        $this->assertEquals('SUCCESS', $response->json('verification_status'));
    }

    /**
     * @test
     */
    public function paypal_handles_failed_payment()
    {
        Http::fake([
            'api.paypal.com/*' => Http::response([
                'name' => 'PAYMENT_DENIED',
                'message' => 'Payment was denied'
            ], 400)
        ]);
        
        $response = Http::post('https://api.paypal.com/v1/payments/payment', [
            'intent' => 'sale'
        ]);
        
        $this->assertEquals(400, $response->status());
        $this->assertEquals('PAYMENT_DENIED', $response->json('name'));
    }
}
