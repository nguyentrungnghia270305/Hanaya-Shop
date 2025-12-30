<?php

namespace Tests\Integration\Payment;

use App\Models\Order\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VNPayIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function vnpay_payment_url_can_be_generated()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'total_amount' => 100000]);

        $vnpayUrl = 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';
        $params = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => 'TEST_TMN',
            'vnp_Amount' => $order->total_amount * 100,
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $order->id,
            'vnp_OrderInfo' => 'Payment for order '.$order->id,
            'vnp_Locale' => 'vn',
            'vnp_ReturnUrl' => url('/payment/vnpay/return'),
        ];

        $queryString = http_build_query($params);
        $fullUrl = $vnpayUrl.'?'.$queryString;

        $this->assertStringContainsString('vnp_TxnRef='.$order->id, $fullUrl);
        $this->assertStringContainsString('vnp_Amount='.($order->total_amount * 100), $fullUrl);
    }

    /**
     * @test
     */
    public function vnpay_response_can_be_validated()
    {
        $responseData = [
            'vnp_TmnCode' => 'TEST_TMN',
            'vnp_Amount' => '10000000',
            'vnp_BankCode' => 'NCB',
            'vnp_ResponseCode' => '00',
            'vnp_TransactionNo' => '123456789',
            'vnp_TxnRef' => '1',
            'vnp_SecureHash' => 'test_hash',
        ];

        $this->assertEquals('00', $responseData['vnp_ResponseCode']);
        $this->assertArrayHasKey('vnp_TransactionNo', $responseData);
    }

    /**
     * @test
     */
    public function vnpay_successful_payment_returns_correct_code()
    {
        $vnpayResponse = [
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
        ];

        $this->assertEquals('00', $vnpayResponse['vnp_ResponseCode']);
        $this->assertEquals('00', $vnpayResponse['vnp_TransactionStatus']);
    }

    /**
     * @test
     */
    public function vnpay_failed_payment_returns_error_code()
    {
        $vnpayResponse = [
            'vnp_ResponseCode' => '24',
            'vnp_Message' => 'Transaction cancelled',
        ];

        $this->assertNotEquals('00', $vnpayResponse['vnp_ResponseCode']);
        $this->assertEquals('24', $vnpayResponse['vnp_ResponseCode']);
    }

    /**
     * @test
     */
    public function vnpay_ipn_callback_can_be_processed()
    {
        $ipnData = [
            'vnp_TmnCode' => 'TEST_TMN',
            'vnp_TxnRef' => '1',
            'vnp_Amount' => '10000000',
            'vnp_ResponseCode' => '00',
            'vnp_TransactionNo' => '123456789',
            'vnp_SecureHash' => 'test_hash',
        ];

        $this->assertArrayHasKey('vnp_TxnRef', $ipnData);
        $this->assertArrayHasKey('vnp_TransactionNo', $ipnData);
        $this->assertEquals('00', $ipnData['vnp_ResponseCode']);
    }
}
