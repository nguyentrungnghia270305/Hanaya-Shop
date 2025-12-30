<?php

namespace Tests\Coverage\ControlFlow\Order;

use App\Models\Address;
use App\Models\Cart\Cart;
use App\Models\Order\Order;
use App\Models\Order\Payment;
use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Payment Flow Branch Coverage Test
 *
 * Tests all branch paths in payment processing including:
 * - Payment method types (COD, Credit Card, PayPal)
 * - Payment status outcomes (success, failed, pending)
 * - Payment validation branches
 * - Transaction ID generation branches
 * - Payment record creation branches
 */
class PaymentFlowBranchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    // ===================================================================
    // CASH ON DELIVERY (COD) BRANCH TESTS
    // ===================================================================

    /** @test */
    public function it_processes_cod_payment_successfully()
    {
        // Arrange: COD payment order
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 2,
        ]);

        $selectedItems = [
            [
                'cart_id' => $cart->id,
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => 2,
                'price' => $product->price,
                'discounted_price' => $product->getDiscountedPrice(),
                'stock_quantity' => $product->stock_quantity,
            ],
        ];

        session(['selectedItems' => $selectedItems]);

        // Act: Checkout with COD
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'selected_items_json' => json_encode($selectedItems),
            'payment_data' => '{}',
        ]);

        // Assert: COD branch - payment pending status
        $response->assertStatus(302);
        $this->assertTrue(str_contains($response->headers->get('Location'), '/checkout/success'));
        $this->assertDatabaseHas('payments', [
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
        ]);
    }

    /** @test */
    public function it_creates_transaction_id_for_cod_payment()
    {
        // Arrange: COD order
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 1,
        ]);

        $selectedItems = [
            [
                'cart_id' => $cart->id,
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => 1,
                'price' => $product->price,
                'discounted_price' => $product->getDiscountedPrice(),
                'stock_quantity' => $product->stock_quantity,
            ],
        ];

        session(['selectedItems' => $selectedItems]);

        // Act: Place COD order
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'selected_items_json' => json_encode($selectedItems),
            'payment_data' => '{}',
        ]);

        // Assert: Transaction ID generated for COD
        $payment = Payment::where('payment_method', 'cash_on_delivery')->first();
        $this->assertNotNull($payment->transaction_id);
        $this->assertStringStartsWith('COD_', $payment->transaction_id);
    }

    // ===================================================================
    // CREDIT CARD BRANCH TESTS
    // ===================================================================

    /** @test */
    public function it_processes_credit_card_payment_successfully()
    {
        // Arrange: Credit card payment
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 1,
        ]);

        $selectedItems = [
            [
                'cart_id' => $cart->id,
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => 1,
                'price' => $product->price,
                'discounted_price' => $product->getDiscountedPrice(),
                'stock_quantity' => $product->stock_quantity,
            ],
        ];

        session(['selectedItems' => $selectedItems]);

        // Act: Checkout with credit card
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'credit_card',
            'selected_items_json' => json_encode($selectedItems),
            'payment_data' => json_encode(['last_digits' => '4242']),
        ]);

        // Assert: Credit card branch - payment completed
        $response->assertStatus(302);
        $this->assertTrue(str_contains($response->headers->get('Location'), '/checkout/success'));
        $this->assertDatabaseHas('payments', [
            'payment_method' => 'credit_card',
            'payment_status' => 'completed',
        ]);
    }

    /** @test */
    public function it_generates_unique_transaction_id_for_credit_card()
    {
        // Arrange: Credit card order
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 1,
        ]);

        $selectedItems = [
            [
                'cart_id' => $cart->id,
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => 1,
                'price' => $product->price,
                'discounted_price' => $product->getDiscountedPrice(),
                'stock_quantity' => $product->stock_quantity,
            ],
        ];

        session(['selectedItems' => $selectedItems]);

        // Act: Place credit card order
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'credit_card',
            'selected_items_json' => json_encode($selectedItems),
            'payment_data' => json_encode(['last_digits' => '1234']),
        ]);

        // Assert: Unique transaction ID for credit card
        $payment = Payment::where('payment_method', 'credit_card')->first();
        $this->assertNotNull($payment->transaction_id);
        $this->assertStringStartsWith('CC_', $payment->transaction_id);
    }

    /** @test */
    public function it_validates_credit_card_payment_data()
    {
        // Arrange: Missing card data
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 1,
        ]);

        $selectedItems = [
            [
                'cart_id' => $cart->id,
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => 1,
                'price' => $product->price,
                'discounted_price' => $product->getDiscountedPrice(),
                'stock_quantity' => $product->stock_quantity,
            ],
        ];

        session(['selectedItems' => $selectedItems]);

        // Act: Attempt payment without card data
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'credit_card',
            'selected_items_json' => json_encode($selectedItems),
            'payment_data' => '{}', // Missing card details
        ]);

        // Assert: Validation branch fails
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // ===================================================================
    // PAYPAL BRANCH TESTS
    // ===================================================================

    /** @test */
    public function it_processes_paypal_payment_successfully()
    {
        // Arrange: PayPal payment
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 1,
        ]);

        $selectedItems = [
            [
                'cart_id' => $cart->id,
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => 1,
                'price' => $product->price,
                'discounted_price' => $product->getDiscountedPrice(),
                'stock_quantity' => $product->stock_quantity,
            ],
        ];

        session(['selectedItems' => $selectedItems]);

        // Act: Checkout with PayPal
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'paypal',
            'selected_items_json' => json_encode($selectedItems),
            'payment_data' => json_encode(['paypal_transaction' => 'PAYPAL-123']),
        ]);

        // Assert: PayPal branch - payment completed
        $response->assertStatus(302);
        $this->assertTrue(str_contains($response->headers->get('Location'), '/checkout/success'));
        $this->assertDatabaseHas('payments', [
            'payment_method' => 'paypal',
            'payment_status' => 'completed',
        ]);
    }

    /** @test */
    public function it_generates_paypal_transaction_id()
    {
        // Arrange: PayPal order
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 1,
        ]);

        $selectedItems = [
            [
                'cart_id' => $cart->id,
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => 1,
                'price' => $product->price,
                'discounted_price' => $product->getDiscountedPrice(),
                'stock_quantity' => $product->stock_quantity,
            ],
        ];

        session(['selectedItems' => $selectedItems]);

        // Act: Place PayPal order
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'paypal',
            'selected_items_json' => json_encode($selectedItems),
            'payment_data' => '{}',
        ]);

        // Assert: PayPal transaction ID generated
        $payment = Payment::where('payment_method', 'paypal')->first();
        $this->assertNotNull($payment->transaction_id);
        $this->assertStringStartsWith('PP_', $payment->transaction_id);
    }

    // ===================================================================
    // PAYMENT METHOD VALIDATION BRANCH TESTS
    // ===================================================================

    /** @test */
    public function it_accepts_valid_payment_methods()
    {
        // Test all valid methods
        $validMethods = ['cash_on_delivery', 'credit_card', 'paypal'];

        foreach ($validMethods as $method) {
            // Arrange
            $user = User::factory()->create();
            $address = Address::factory()->create(['user_id' => $user->id]);
            $product = Product::factory()->create(['stock_quantity' => 10]);
            $cart = Cart::factory()->create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'quantity' => 1,
            ]);

            $selectedItems = [
                [
                    'cart_id' => $cart->id,
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => 1,
                    'price' => $product->price,
                    'discounted_price' => $product->getDiscountedPrice(),
                    'stock_quantity' => $product->stock_quantity,
                ],
            ];

            session(['selectedItems' => $selectedItems]);

            $paymentData = $method === 'credit_card' ? json_encode(['last_digits' => '4242']) : '{}';

            // Act: Checkout with valid method
            $response = $this->actingAs($user)->post(route('checkout.store'), [
                'address_id' => $address->id,
                'payment_method' => $method,
                'selected_items_json' => json_encode($selectedItems),
                'payment_data' => $paymentData,
            ]);

            // Assert: Valid method branch accepted
            $response->assertStatus(302);
            $this->assertTrue(str_contains($response->headers->get('Location'), '/checkout/success'));
        }
    }

    /** @test */
    public function it_rejects_invalid_payment_method_string()
    {
        // Arrange: Invalid payment method
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 1,
        ]);

        $selectedItems = [
            [
                'cart_id' => $cart->id,
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => 1,
                'price' => $product->price,
                'discounted_price' => $product->getDiscountedPrice(),
                'stock_quantity' => $product->stock_quantity,
            ],
        ];

        session(['selectedItems' => $selectedItems]);

        // Act: Submit with invalid method
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'cryptocurrency',
            'selected_items_json' => json_encode($selectedItems),
            'payment_data' => '{}',
        ]);

        // Assert: Invalid method branch rejected
        $response->assertSessionHasErrors('payment_method');
    }

    /** @test */
    public function it_rejects_empty_payment_method()
    {
        // Arrange: Empty payment method
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 1,
        ]);

        $selectedItems = [
            [
                'cart_id' => $cart->id,
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => 1,
                'price' => $product->price,
                'discounted_price' => $product->getDiscountedPrice(),
                'stock_quantity' => $product->stock_quantity,
            ],
        ];

        session(['selectedItems' => $selectedItems]);

        // Act: Submit with empty method
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => '',
            'selected_items_json' => json_encode($selectedItems),
            'payment_data' => '{}',
        ]);

        // Assert: Empty method branch rejected
        $response->assertSessionHasErrors('payment_method');
    }

    // ===================================================================
    // PAYMENT STATUS BRANCH TESTS
    // ===================================================================

    /** @test */
    public function it_sets_completed_status_for_online_payments()
    {
        // Arrange: Online payment (credit card)
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 1,
        ]);

        $selectedItems = [
            [
                'cart_id' => $cart->id,
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => 1,
                'price' => $product->price,
                'discounted_price' => $product->getDiscountedPrice(),
                'stock_quantity' => $product->stock_quantity,
            ],
        ];

        session(['selectedItems' => $selectedItems]);

        // Act: Complete online payment
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'credit_card',
            'selected_items_json' => json_encode($selectedItems),
            'payment_data' => json_encode(['last_digits' => '4242']),
        ]);

        // Assert: Status branch - completed
        $this->assertDatabaseHas('payments', [
            'payment_method' => 'credit_card',
            'payment_status' => 'completed',
        ]);
    }

    /** @test */
    public function it_sets_pending_status_for_cod_payments()
    {
        // Arrange: COD payment
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 1,
        ]);

        $selectedItems = [
            [
                'cart_id' => $cart->id,
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => 1,
                'price' => $product->price,
                'discounted_price' => $product->getDiscountedPrice(),
                'stock_quantity' => $product->stock_quantity,
            ],
        ];

        session(['selectedItems' => $selectedItems]);

        // Act: COD payment
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'selected_items_json' => json_encode($selectedItems),
            'payment_data' => '{}',
        ]);

        // Assert: Status branch - pending
        $this->assertDatabaseHas('payments', [
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
        ]);
    }

    // ===================================================================
    // PAYMENT DATA PARSING BRANCH TESTS
    // ===================================================================

    /** @test */
    public function it_parses_valid_json_payment_data()
    {
        // Arrange: Valid JSON payment data
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 1,
        ]);

        $selectedItems = [
            [
                'cart_id' => $cart->id,
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => 1,
                'price' => $product->price,
                'discounted_price' => $product->getDiscountedPrice(),
                'stock_quantity' => $product->stock_quantity,
            ],
        ];

        session(['selectedItems' => $selectedItems]);

        // Act: Submit with valid JSON
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'credit_card',
            'selected_items_json' => json_encode($selectedItems),
            'payment_data' => json_encode(['last_digits' => '9999']),
        ]);

        // Assert: JSON parsing branch success
        $response->assertStatus(302);
        $this->assertTrue(str_contains($response->headers->get('Location'), '/checkout/success'));
    }

    /** @test */
    public function it_handles_invalid_json_payment_data_gracefully()
    {
        // Arrange: Invalid JSON
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 1,
        ]);

        $selectedItems = [
            [
                'cart_id' => $cart->id,
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => 1,
                'price' => $product->price,
                'discounted_price' => $product->getDiscountedPrice(),
                'stock_quantity' => $product->stock_quantity,
            ],
        ];

        session(['selectedItems' => $selectedItems]);

        // Act: Submit with malformed JSON
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'selected_items_json' => json_encode($selectedItems),
            'payment_data' => '{invalid json',
        ]);

        // Assert: Invalid JSON branch handled
        // COD should still work even with invalid payment_data
        $response->assertStatus(302);
        $this->assertTrue(str_contains($response->headers->get('Location'), '/checkout/success'));
    }

    // ===================================================================
    // PAYMENT RECORD CREATION BRANCH TESTS
    // ===================================================================

    /** @test */
    public function it_creates_payment_record_with_order_association()
    {
        // Arrange: Payment order
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 1,
        ]);

        $selectedItems = [
            [
                'cart_id' => $cart->id,
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => 1,
                'price' => $product->price,
                'discounted_price' => $product->getDiscountedPrice(),
                'stock_quantity' => $product->stock_quantity,
            ],
        ];

        session(['selectedItems' => $selectedItems]);

        // Act: Complete checkout
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'selected_items_json' => json_encode($selectedItems),
            'payment_data' => '{}',
        ]);

        // Assert: Payment record created with order_id
        $order = Order::where('user_id', $user->id)->first();
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => 'cash_on_delivery',
        ]);
    }

    /** @test */
    public function it_creates_unique_payments_for_different_orders()
    {
        // Arrange: Two separate orders
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);

        $product1 = Product::factory()->create(['stock_quantity' => 10]);
        $product2 = Product::factory()->create(['stock_quantity' => 10]);

        // Order 1
        $cart1 = Cart::factory()->create([
            'product_id' => $product1->id,
            'user_id' => $user->id,
            'quantity' => 1,
        ]);

        $selectedItems1 = [
            [
                'cart_id' => $cart1->id,
                'id' => $product1->id,
                'name' => $product1->name,
                'quantity' => 1,
                'price' => $product1->price,
                'discounted_price' => $product1->getDiscountedPrice(),
                'stock_quantity' => $product1->stock_quantity,
            ],
        ];

        session(['selectedItems' => $selectedItems1]);

        $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'selected_items_json' => json_encode($selectedItems1),
            'payment_data' => '{}',
        ]);

        // Order 2
        $cart2 = Cart::factory()->create([
            'product_id' => $product2->id,
            'user_id' => $user->id,
            'quantity' => 1,
        ]);

        $selectedItems2 = [
            [
                'cart_id' => $cart2->id,
                'id' => $product2->id,
                'name' => $product2->name,
                'quantity' => 1,
                'price' => $product2->price,
                'discounted_price' => $product2->getDiscountedPrice(),
                'stock_quantity' => $product2->stock_quantity,
            ],
        ];

        session(['selectedItems' => $selectedItems2]);

        $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'credit_card',
            'selected_items_json' => json_encode($selectedItems2),
            'payment_data' => json_encode(['last_digits' => '4242']),
        ]);

        // Assert: Two unique payments created
        $this->assertEquals(2, Payment::count());
        $this->assertCount(2, Payment::all());
    }
}
