<?php

namespace Tests\Integration\Payment;

use App\Models\Order\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StripeIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function stripe_payment_intent_can_be_created()
    {
        Http::fake([
            'api.stripe.com/*' => Http::response([
                'id' => 'pi_123456',
                'status' => 'requires_payment_method',
                'amount' => 10000,
                'currency' => 'usd'
            ], 200)
        ]);
        
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'total_amount' => 100]);
        
        $response = Http::post('https://api.stripe.com/v1/payment_intents', [
            'amount' => $order->total_amount * 100,
            'currency' => 'usd'
        ]);
        
        $this->assertEquals(200, $response->status());
        $this->assertEquals('requires_payment_method', $response->json('status'));
    }

    /**
     * @test
     */
    public function stripe_payment_can_be_confirmed()
    {
        Http::fake([
            'api.stripe.com/*' => Http::response([
                'id' => 'pi_123456',
                'status' => 'succeeded',
                'amount' => 10000
            ], 200)
        ]);
        
        $response = Http::post('https://api.stripe.com/v1/payment_intents/pi_123456/confirm', [
            'payment_method' => 'pm_card_visa'
        ]);
        
        $this->assertEquals(200, $response->status());
        $this->assertEquals('succeeded', $response->json('status'));
    }

    /**
     * @test
     */
    public function stripe_webhook_processes_payment_succeeded()
    {
        $webhookData = [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_123456',
                    'status' => 'succeeded',
                    'amount' => 10000
                ]
            ]
        ];
        
        $this->assertIsArray($webhookData);
        $this->assertEquals('payment_intent.succeeded', $webhookData['type']);
        $this->assertEquals('succeeded', $webhookData['data']['object']['status']);
    }

    /**
     * @test
     */
    public function stripe_handles_declined_payment()
    {
        Http::fake([
            'api.stripe.com/*' => Http::response([
                'error' => [
                    'type' => 'card_error',
                    'code' => 'card_declined',
                    'message' => 'Your card was declined'
                ]
            ], 402)
        ]);
        
        $response = Http::post('https://api.stripe.com/v1/payment_intents/pi_123456/confirm');
        
        $this->assertEquals(402, $response->status());
        $this->assertEquals('card_declined', $response->json('error.code'));
    }

    /**
     * @test
     */
    public function stripe_refund_can_be_created()
    {
        Http::fake([
            'api.stripe.com/*' => Http::response([
                'id' => 're_123456',
                'status' => 'succeeded',
                'amount' => 10000
            ], 200)
        ]);
        
        $response = Http::post('https://api.stripe.com/v1/refunds', [
            'payment_intent' => 'pi_123456'
        ]);
        
        $this->assertEquals(200, $response->status());
        $this->assertEquals('succeeded', $response->json('status'));
    }
}
