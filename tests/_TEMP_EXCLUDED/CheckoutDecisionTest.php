<?php

namespace Tests\Coverage\ControlFlow\Cart;

use App\Models\Address;
use App\Models\Cart\Cart;
use App\Models\Order\Payment;
use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Checkout Decision Coverage Test
 *
 * Tests all decision points in checkout flow including:
 * - Cart empty/not empty decisions
 * - Address validation decisions (exists, valid format)
 * - Payment method selection decisions
 * - Stock availability at checkout decisions
 * - Selected items validation decisions
 */
class CheckoutDecisionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    // ===================================================================
    // CART EMPTY DECISION TESTS
    // ===================================================================

    /** @test */
    public function it_proceeds_to_checkout_when_cart_has_items()
    {
        // Arrange: Cart with items
        $user = User::factory()->create();
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

        // Act: Proceed to checkout with items
        $response = $this->actingAs($user)->post(route('checkout.preview'), [
            'selected_items_json' => json_encode($selectedItems),
        ]);

        // Assert: Decision TRUE - cart has items, proceed
        $response->assertRedirect(route('checkout.index'));
        $this->assertNotNull(session('selectedItems'));
    }

    /** @test */
    public function it_redirects_back_when_cart_is_empty()
    {
        // Arrange: Empty cart
        $user = User::factory()->create();

        // Act: Preview stores empty array, then index checks and redirects
        $this->actingAs($user)->post(route('checkout.preview'), [
            'selected_items_json' => json_encode([]),
        ]);

        // Assert: Decision FALSE - cart empty detected at checkout.index
        $response = $this->actingAs($user)->get(route('checkout.index'));
        $response->assertRedirect();
    }

    // ===================================================================
    // ADDRESS VALIDATION DECISION TESTS
    // ===================================================================

    /** @test */
    public function it_proceeds_when_valid_address_selected()
    {
        // Arrange: Valid address exists
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

        // Act: Submit checkout with valid address
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'selected_items_json' => json_encode($selectedItems),
            'payment_data' => '{}',
        ]);

        // Assert: Decision TRUE - valid address, order created
        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'address_id' => $address->id,
        ]);
    }

    /** @test */
    public function it_rejects_checkout_when_no_address_selected()
    {
        // Arrange: No address provided
        $user = User::factory()->create();
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

        // Act: Submit without address
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'payment_method' => 'cash_on_delivery',
            'selected_items_json' => json_encode($selectedItems),
            'payment_data' => '{}',
        ]);

        // Assert: Decision FALSE - no address, validation error
        $response->assertSessionHasErrors('address_id');
    }

    /** @test */
    public function it_rejects_checkout_when_address_does_not_exist()
    {
        // Arrange: Invalid address ID
        $user = User::factory()->create();
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

        // Act: Submit with non-existent address
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => 99999,
            'payment_method' => 'cash_on_delivery',
            'selected_items_json' => json_encode($selectedItems),
            'payment_data' => '{}',
        ]);

        // Assert: Decision FALSE - address not exists, validation error
        $response->assertSessionHasErrors('address_id');
    }

    // ===================================================================
    // PAYMENT METHOD DECISION TESTS
    // ===================================================================

    /** @test */
    public function it_proceeds_with_valid_payment_method_cod()
    {
        // Arrange: COD payment method
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

        // Act: Checkout with COD
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'selected_items_json' => json_encode($selectedItems),
            'payment_data' => '{}',
        ]);

        // Assert: Decision TRUE - valid payment method
        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('payments', [
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
        ]);
    }

    /** @test */
    public function it_proceeds_with_valid_payment_method_credit_card()
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

        // Assert: Decision TRUE - credit card accepted
        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('payments', [
            'payment_method' => 'credit_card',
            'payment_status' => 'completed',
        ]);
    }

    /** @test */
    public function it_rejects_checkout_with_invalid_payment_method()
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

        // Act: Submit with invalid payment method
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'bitcoin',
            'selected_items_json' => json_encode($selectedItems),
            'payment_data' => '{}',
        ]);

        // Assert: Decision FALSE - invalid payment method
        $response->assertSessionHasErrors('payment_method');
    }

    /** @test */
    public function it_rejects_checkout_when_payment_method_missing()
    {
        // Arrange: No payment method provided
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

        // Act: Submit without payment method
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'selected_items_json' => json_encode($selectedItems),
            'payment_data' => '{}',
        ]);

        // Assert: Decision FALSE - payment method required
        $response->assertSessionHasErrors('payment_method');
    }

    // ===================================================================
    // STOCK AVAILABILITY AT CHECKOUT DECISION TESTS
    // ===================================================================

    /** @test */
    public function it_proceeds_when_all_items_have_sufficient_stock()
    {
        // Arrange: All items in stock
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 5,
        ]);

        $selectedItems = [
            [
                'cart_id' => $cart->id,
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => 5,
                'price' => $product->price,
                'discounted_price' => $product->getDiscountedPrice(),
                'stock_quantity' => $product->stock_quantity,
            ],
        ];

        session(['selectedItems' => $selectedItems]);

        // Act: Checkout with sufficient stock
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'selected_items_json' => json_encode($selectedItems),
            'payment_data' => '{}',
        ]);

        // Assert: Decision TRUE - sufficient stock, order placed
        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function it_rejects_checkout_when_item_out_of_stock()
    {
        // Arrange: Item out of stock
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['stock_quantity' => 0]);
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
                'stock_quantity' => 0,
            ],
        ];

        // Act: Attempt checkout with out of stock item
        $response = $this->actingAs($user)->post(route('checkout.preview'), [
            'selected_items_json' => json_encode($selectedItems),
        ]);

        // Assert: Decision FALSE - insufficient stock, error
        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    /** @test */
    public function it_rejects_checkout_when_quantity_exceeds_stock()
    {
        // Arrange: Quantity exceeds available stock
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 3]);
        $cart = Cart::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 5,
        ]);

        $selectedItems = [
            [
                'cart_id' => $cart->id,
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => 5,
                'price' => $product->price,
                'discounted_price' => $product->getDiscountedPrice(),
                'stock_quantity' => 3,
            ],
        ];

        // Act: Attempt checkout with excessive quantity
        $response = $this->actingAs($user)->post(route('checkout.preview'), [
            'selected_items_json' => json_encode($selectedItems),
        ]);

        // Assert: Decision FALSE - quantity exceeds stock
        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    // ===================================================================
    // SELECTED ITEMS VALIDATION DECISION TESTS
    // ===================================================================

    /** @test */
    public function it_proceeds_when_selected_items_json_is_valid()
    {
        // Arrange: Valid JSON items
        $user = User::factory()->create();
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

        // Act: Submit with valid JSON
        $response = $this->actingAs($user)->post(route('checkout.preview'), [
            'selected_items_json' => json_encode($selectedItems),
        ]);

        // Assert: Decision TRUE - valid JSON parsed
        $response->assertRedirect(route('checkout.index'));
    }

    /** @test */
    public function it_handles_checkout_when_no_items_in_session()
    {
        // Arrange: Session has no selected items
        $user = User::factory()->create();
        Address::factory()->create(['user_id' => $user->id]);

        // Act: Access checkout page without session items
        $response = $this->actingAs($user)->get(route('checkout.index'));

        // Assert: Decision FALSE - no items in session, redirect
        $response->assertRedirect();
    }

    // ===================================================================
    // USER AUTHENTICATION DECISION TESTS
    // ===================================================================

    /** @test */
    public function it_allows_checkout_for_authenticated_user()
    {
        // Arrange: Authenticated user
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

        // Act: Checkout as authenticated user
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'selected_items_json' => json_encode($selectedItems),
            'payment_data' => '{}',
        ]);

        // Assert: Decision TRUE - authenticated user allowed
        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
        ]);
    }

    // ===================================================================
    // MULTIPLE ITEMS DECISION TESTS
    // ===================================================================

    /** @test */
    public function it_processes_checkout_with_multiple_items_all_valid()
    {
        // Arrange: Multiple items, all valid
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);

        $product1 = Product::factory()->create(['stock_quantity' => 10]);
        $product2 = Product::factory()->create(['stock_quantity' => 20]);

        $cart1 = Cart::factory()->create([
            'product_id' => $product1->id,
            'user_id' => $user->id,
            'quantity' => 2,
        ]);
        $cart2 = Cart::factory()->create([
            'product_id' => $product2->id,
            'user_id' => $user->id,
            'quantity' => 3,
        ]);

        $selectedItems = [
            [
                'cart_id' => $cart1->id,
                'id' => $product1->id,
                'name' => $product1->name,
                'quantity' => 2,
                'price' => $product1->price,
                'discounted_price' => $product1->getDiscountedPrice(),
                'stock_quantity' => $product1->stock_quantity,
            ],
            [
                'cart_id' => $cart2->id,
                'id' => $product2->id,
                'name' => $product2->name,
                'quantity' => 3,
                'price' => $product2->price,
                'discounted_price' => $product2->getDiscountedPrice(),
                'stock_quantity' => $product2->stock_quantity,
            ],
        ];

        session(['selectedItems' => $selectedItems]);

        // Act: Checkout with multiple valid items
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'address_id' => $address->id,
            'payment_method' => 'cash_on_delivery',
            'selected_items_json' => json_encode($selectedItems),
            'payment_data' => '{}',
        ]);

        // Assert: Decision TRUE - all items valid, order placed
        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function it_rejects_checkout_when_one_item_invalid_in_multiple()
    {
        // Arrange: Multiple items, one invalid (out of stock)
        $user = User::factory()->create();

        $product1 = Product::factory()->create(['stock_quantity' => 10]);
        $product2 = Product::factory()->create(['stock_quantity' => 0]); // Out of stock

        $cart1 = Cart::factory()->create([
            'product_id' => $product1->id,
            'user_id' => $user->id,
            'quantity' => 2,
        ]);
        $cart2 = Cart::factory()->create([
            'product_id' => $product2->id,
            'user_id' => $user->id,
            'quantity' => 1,
        ]);

        $selectedItems = [
            [
                'cart_id' => $cart1->id,
                'id' => $product1->id,
                'name' => $product1->name,
                'quantity' => 2,
                'price' => $product1->price,
                'discounted_price' => $product1->getDiscountedPrice(),
                'stock_quantity' => $product1->stock_quantity,
            ],
            [
                'cart_id' => $cart2->id,
                'id' => $product2->id,
                'name' => $product2->name,
                'quantity' => 1,
                'price' => $product2->price,
                'discounted_price' => $product2->getDiscountedPrice(),
                'stock_quantity' => 0,
            ],
        ];

        // Act: Attempt checkout with one invalid item
        $response = $this->actingAs($user)->post(route('checkout.preview'), [
            'selected_items_json' => json_encode($selectedItems),
        ]);

        // Assert: Decision FALSE - one item invalid, reject all
        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }
}
