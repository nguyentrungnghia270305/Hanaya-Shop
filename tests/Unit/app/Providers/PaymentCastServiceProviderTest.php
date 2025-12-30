<?php

namespace Tests\Unit\App\Providers;

use App\Models\Order\Order;
use App\Models\Order\Payment;
use App\Providers\PaymentCastServiceProvider;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentCastServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function provider_is_registered_in_application()
    {
        $providers = $this->app->getLoadedProviders();

        $this->assertArrayHasKey(PaymentCastServiceProvider::class, $providers);
    }

    /**
     * @test
     */
    public function provider_validates_payment_method_on_creation()
    {
        $order = Order::factory()->create();

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'credit_card',
            'payment_status' => 'pending',
        ]);

        $this->assertEquals('credit_card', $payment->payment_method);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'payment_method' => 'credit_card',
        ]);
    }

    /**
     * @test
     */
    public function provider_rejects_invalid_payment_method()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid payment method');

        $order = Order::factory()->create();

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'bitcoin',
            'payment_status' => 'pending',
        ]);
    }

    /**
     * @test
     */
    public function provider_allows_all_valid_payment_methods()
    {
        $order = Order::factory()->create();
        $validMethods = ['credit_card', 'paypal', 'cash_on_delivery'];

        foreach ($validMethods as $method) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method' => $method,
                'payment_status' => 'pending',
            ]);

            $this->assertEquals($method, $payment->payment_method);
        }
    }

    /**
     * @test
     */
    public function provider_sanitizes_payment_method_with_whitespace()
    {
        $order = Order::factory()->create();

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => '  paypal  ',
            'payment_status' => 'pending',
        ]);

        $this->assertEquals('paypal', $payment->payment_method);
    }

    /**
     * @test
     */
    public function provider_configuration_exists()
    {
        $providersPath = base_path('bootstrap/providers.php');

        $this->assertFileExists($providersPath);

        $providers = require $providersPath;
        $this->assertContains(PaymentCastServiceProvider::class, $providers);
    }
}
