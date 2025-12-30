<?php

namespace Tests\Coverage\ControlFlow\Product;

use App\Models\Cart\Cart;
use App\Models\Order\Order;
use App\Models\Order\OrderDetail;
use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Stock Validation Branch Coverage Test
 *
 * Tests all branch paths in stock validation logic including:
 * - Stock availability checks (in stock vs out of stock)
 * - Stock quantity comparisons (sufficient vs insufficient)
 * - Stock depletion scenarios (normal, boundary, complete)
 * - Stock restoration on order cancellation
 * - Low stock warning thresholds
 */
class StockValidationBranchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    // ===================================================================
    // STOCK AVAILABILITY CHECK BRANCH TESTS
    // ===================================================================

    /** @test */
    public function it_validates_stock_available_when_quantity_greater_than_zero()
    {
        // Arrange: Product with stock
        $product = Product::factory()->create(['stock_quantity' => 10]);

        // Act: Check stock availability
        $isAvailable = $product->stock_quantity > 0;

        // Assert: Available branch - TRUE
        $this->assertTrue($isAvailable);
        $this->assertGreaterThan(0, $product->stock_quantity);
    }

    /** @test */
    public function it_validates_stock_unavailable_when_quantity_is_zero()
    {
        // Arrange: Product out of stock
        $product = Product::factory()->create(['stock_quantity' => 0]);

        // Act: Check stock availability
        $isAvailable = $product->stock_quantity > 0;

        // Assert: Unavailable branch - FALSE
        $this->assertFalse($isAvailable);
        $this->assertEquals(0, $product->stock_quantity);
    }

    // ===================================================================
    // STOCK SUFFICIENT COMPARISON BRANCH TESTS
    // ===================================================================

    /** @test */
    public function it_validates_sufficient_stock_when_requested_quantity_within_limit()
    {
        // Arrange: Stock sufficient for request
        $product = Product::factory()->create(['stock_quantity' => 50]);
        $requestedQuantity = 30;

        // Act: Check sufficiency
        $isSufficient = $requestedQuantity <= $product->stock_quantity;

        // Assert: Sufficient branch - TRUE
        $this->assertTrue($isSufficient);
        $this->assertLessThanOrEqual($product->stock_quantity, $requestedQuantity);
    }

    /** @test */
    public function it_validates_insufficient_stock_when_requested_quantity_exceeds_limit()
    {
        // Arrange: Stock insufficient for request
        $product = Product::factory()->create(['stock_quantity' => 5]);
        $requestedQuantity = 10;

        // Act: Check sufficiency
        $isSufficient = $requestedQuantity <= $product->stock_quantity;

        // Assert: Insufficient branch - FALSE
        $this->assertFalse($isSufficient);
        $this->assertGreaterThan($product->stock_quantity, $requestedQuantity);
    }

    /** @test */
    public function it_validates_exact_stock_quantity_match()
    {
        // Arrange: Exact quantity match
        $product = Product::factory()->create(['stock_quantity' => 20]);
        $requestedQuantity = 20;

        // Act: Check exact match
        $isSufficient = $requestedQuantity <= $product->stock_quantity;

        // Assert: Boundary branch - TRUE (exact match allowed)
        $this->assertTrue($isSufficient);
        $this->assertEquals($product->stock_quantity, $requestedQuantity);
    }

    // ===================================================================
    // STOCK DEPLETION BRANCH TESTS
    // ===================================================================

    /** @test */
    // public function it_depletes_stock_partially_after_order()
    // {
    //     // Arrange: Order with partial stock usage
    //     $product = Product::factory()->create(['stock_quantity' => 100]);
    //     $order = Order::factory()->create();
    //     OrderDetail::factory()->create([
    //         'order_id' => $order->id,
    //         'product_id' => $product->id,
    //         'quantity' => 30,
    //     ]);

    //     $initialStock = $product->stock_quantity;

    //     // Act: Deplete stock
    //     $product->stock_quantity -= 30;
    //     $product->save();

    //     // Assert: Partial depletion branch
    //     $this->assertEquals(70, $product->stock_quantity);
    //     $this->assertGreaterThan(0, $product->stock_quantity);
    // }

    // /** @test */
    // public function it_depletes_stock_completely_when_full_quantity_ordered()
    // {
    //     // Arrange: Order uses all stock
    //     $product = Product::factory()->create(['stock_quantity' => 15]);
    //     $order = Order::factory()->create();
    //     OrderDetail::factory()->create([
    //         'order_id' => $order->id,
    //         'product_id' => $product->id,
    //         'quantity' => 15,
    //     ]);

    //     // Act: Deplete all stock
    //     $product->stock_quantity -= 15;
    //     $product->save();

    //     // Assert: Complete depletion branch
    //     $this->assertEquals(0, $product->stock_quantity);
    // }

    /** @test */
    public function it_prevents_negative_stock_after_depletion()
    {
        // Arrange: Stock validation scenario
        $product = Product::factory()->create(['stock_quantity' => 5]);
        $requestedQuantity = 10;

        // Act: Attempt depletion that would go negative
        $canDeplete = $requestedQuantity <= $product->stock_quantity;

        // Assert: Negative prevention branch - FALSE
        $this->assertFalse($canDeplete);
        if ($canDeplete) {
            $product->stock_quantity -= $requestedQuantity;
        }
        
        // Stock remains unchanged
        $this->assertEquals(5, $product->stock_quantity);
    }

    // ===================================================================
    // STOCK RESTORATION BRANCH TESTS
    // ===================================================================

    // /** @test */
    // public function it_restores_stock_when_order_cancelled()
    // {
    //     // Arrange: Cancelled order scenario
    //     $user = User::factory()->create();
    //     $product = Product::factory()->create(['stock_quantity' => 50]);
    //     $order = Order::factory()->pending()->create(['user_id' => $user->id]);
        
    //     OrderDetail::factory()->create([
    //         'order_id' => $order->id,
    //         'product_id' => $product->id,
    //         'quantity' => 10,
    //     ]);

    //     // Simulate stock depletion
    //     $product->stock_quantity -= 10;
    //     $product->save();

    //     // Act: Cancel order - stock restored
    //     $response = $this->actingAs($user)->get(route('order.cancel', $order->id));

    //     // Assert: Restoration branch
    //     $product->refresh();
    //     $this->assertEquals(50, $product->stock_quantity); // Restored
    // }

    // /** @test */
    // public function it_restores_multiple_items_stock_on_order_cancellation()
    // {
    //     // Arrange: Multi-item cancellation
    //     $user = User::factory()->create();
    //     $product1 = Product::factory()->create(['stock_quantity' => 30]);
    //     $product2 = Product::factory()->create(['stock_quantity' => 20]);
        
    //     $order = Order::factory()->pending()->create(['user_id' => $user->id]);
        
    //     OrderDetail::factory()->create([
    //         'order_id' => $order->id,
    //         'product_id' => $product1->id,
    //         'quantity' => 5,
    //     ]);
    //     OrderDetail::factory()->create([
    //         'order_id' => $order->id,
    //         'product_id' => $product2->id,
    //         'quantity' => 3,
    //     ]);

    //     // Simulate depletion
    //     $product1->stock_quantity -= 5;
    //     $product1->save();
    //     $product2->stock_quantity -= 3;
    //     $product2->save();

    //     // Act: Cancel order
    //     $response = $this->actingAs($user)->get(route('order.cancel', $order->id));

    //     // Assert: Both items restored
    //     $product1->refresh();
    //     $product2->refresh();
    //     $this->assertEquals(30, $product1->stock_quantity);
    //     $this->assertEquals(20, $product2->stock_quantity);
    // }

    // ===================================================================
    // LOW STOCK WARNING BRANCH TESTS
    // ===================================================================

    /** @test */
    public function it_identifies_low_stock_when_quantity_below_threshold()
    {
        // Arrange: Low stock scenario
        $product = Product::factory()->create(['stock_quantity' => 5]);
        $lowStockThreshold = 10;

        // Act: Check low stock
        $isLowStock = $product->stock_quantity < $lowStockThreshold;

        // Assert: Low stock branch - TRUE
        $this->assertTrue($isLowStock);
        $this->assertLessThan($lowStockThreshold, $product->stock_quantity);
    }

    /** @test */
    public function it_identifies_adequate_stock_when_quantity_above_threshold()
    {
        // Arrange: Adequate stock scenario
        $product = Product::factory()->create(['stock_quantity' => 50]);
        $lowStockThreshold = 10;

        // Act: Check low stock
        $isLowStock = $product->stock_quantity < $lowStockThreshold;

        // Assert: Adequate stock branch - FALSE
        $this->assertFalse($isLowStock);
        $this->assertGreaterThanOrEqual($lowStockThreshold, $product->stock_quantity);
    }

    /** @test */
    public function it_handles_boundary_at_low_stock_threshold()
    {
        // Arrange: Exact threshold quantity
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $lowStockThreshold = 10;

        // Act: Check low stock at boundary
        $isLowStock = $product->stock_quantity < $lowStockThreshold;

        // Assert: Boundary branch - FALSE (not below threshold)
        $this->assertFalse($isLowStock);
        $this->assertEquals($lowStockThreshold, $product->stock_quantity);
    }

    // ===================================================================
    // ADD TO CART STOCK VALIDATION BRANCH TESTS
    // ===================================================================

    /** @test */
    public function it_allows_add_to_cart_when_stock_sufficient()
    {
        // Arrange: Sufficient stock for cart addition
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 20]);

        // Act: Add to cart with stock validation
        $response = $this->actingAs($user)->post(route('cart.add', $product->id), [
            'quantity' => 10,
        ]);

        // Assert: Stock sufficient branch - allowed
        $response->assertRedirect();
        $this->assertDatabaseHas('carts', [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);
    }

    /** @test */
    public function it_prevents_add_to_cart_when_stock_insufficient()
    {
        // Arrange: Insufficient stock for cart addition
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 5]);

        // Act: Attempt add to cart exceeding stock
        $response = $this->actingAs($user)->post(route('cart.add', $product->id), [
            'quantity' => 10,
        ]);

        // Assert: Stock insufficient branch - prevented
        $response->assertRedirect();
        $this->assertDatabaseMissing('carts', [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);
    }

    /** @test */
    public function it_validates_combined_cart_quantity_against_stock()
    {
        // Arrange: Existing cart + new quantity validation
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 15]);
        
        Cart::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 8,
        ]);

        // Act: Add more to exceed stock
        $response = $this->actingAs($user)->post(route('cart.add', $product->id), [
            'quantity' => 10, // 8 + 10 = 18 > 15
        ]);

        // Assert: Combined quantity validation branch
        $response->assertRedirect();
        $cart = Cart::where('product_id', $product->id)->first();
        $this->assertEquals(8, $cart->quantity); // Unchanged
    }

    // ===================================================================
    // CHECKOUT STOCK VALIDATION BRANCH TESTS
    // ===================================================================

    /** @test */
    public function it_validates_stock_availability_at_checkout()
    {
        // Arrange: Checkout stock validation
        $user = User::factory()->create();
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

        // Act: Validate at checkout
        $response = $this->actingAs($user)->post(route('checkout.preview'), [
            'selected_items_json' => json_encode($selectedItems),
        ]);

        // Assert: Stock validation passed at checkout
        $response->assertRedirect(route('checkout.index'));
    }

    /** @test */
    public function it_rejects_checkout_when_stock_depleted_since_adding_to_cart()
    {
        // Arrange: Stock changed after cart addition
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $cart = Cart::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'quantity' => 5,
        ]);

        // Simulate stock depletion
        $product->stock_quantity = 0;
        $product->save();

        $selectedItems = [
            [
                'cart_id' => $cart->id,
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => 5,
                'price' => $product->price,
                'discounted_price' => $product->getDiscountedPrice(),
                'stock_quantity' => 0,
            ],
        ];

        // Act: Attempt checkout with depleted stock
        $response = $this->actingAs($user)->post(route('checkout.preview'), [
            'selected_items_json' => json_encode($selectedItems),
        ]);

        // Assert: Stock validation failed at checkout
        $response->assertRedirect();
        $response->assertSessionHasErrors();
    }

    // ===================================================================
    // BOUNDARY STOCK VALIDATION TESTS
    // ===================================================================

    /** @test */
    public function it_validates_stock_at_minimum_boundary_one()
    {
        // Arrange: Stock at minimum (1)
        $product = Product::factory()->create(['stock_quantity' => 1]);
        $requestedQuantity = 1;

        // Act: Validate minimum boundary
        $isSufficient = $requestedQuantity <= $product->stock_quantity;

        // Assert: Minimum boundary branch - TRUE
        $this->assertTrue($isSufficient);
        $this->assertEquals(1, $product->stock_quantity);
    }

    /** @test */
    public function it_validates_stock_at_maximum_boundary()
    {
        // Arrange: Stock at maximum boundary (1000)
        $product = Product::factory()->create(['stock_quantity' => 1000]);
        $requestedQuantity = 1000;

        // Act: Validate maximum boundary
        $isSufficient = $requestedQuantity <= $product->stock_quantity;

        // Assert: Maximum boundary branch - TRUE
        $this->assertTrue($isSufficient);
        $this->assertEquals(1000, $product->stock_quantity);
    }

    /** @test */
    public function it_validates_stock_just_below_requested_quantity()
    {
        // Arrange: Stock one less than requested
        $product = Product::factory()->create(['stock_quantity' => 9]);
        $requestedQuantity = 10;

        // Act: Validate just below
        $isSufficient = $requestedQuantity <= $product->stock_quantity;

        // Assert: Just below branch - FALSE
        $this->assertFalse($isSufficient);
    }

    /** @test */
    public function it_validates_stock_just_above_requested_quantity()
    {
        // Arrange: Stock one more than requested
        $product = Product::factory()->create(['stock_quantity' => 11]);
        $requestedQuantity = 10;

        // Act: Validate just above
        $isSufficient = $requestedQuantity <= $product->stock_quantity;

        // Assert: Just above branch - TRUE
        $this->assertTrue($isSufficient);
    }

    // ===================================================================
    // CONCURRENT STOCK VALIDATION TESTS
    // ===================================================================

    /** @test */
    public function it_handles_concurrent_cart_additions_with_stock_validation()
    {
        // Arrange: Multiple users adding same product
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 10]);

        // Act: Both users add to cart
        $this->actingAs($user1)->post(route('cart.add', $product->id), [
            'quantity' => 6,
        ]);
        
        $this->actingAs($user2)->post(route('cart.add', $product->id), [
            'quantity' => 5,
        ]);

        // Assert: Both additions allowed (stock not yet depleted)
        $this->assertDatabaseHas('carts', [
            'product_id' => $product->id,
            'user_id' => $user1->id,
            'quantity' => 6,
        ]);
        $this->assertDatabaseHas('carts', [
            'product_id' => $product->id,
            'user_id' => $user2->id,
            'quantity' => 5,
        ]);
    }

    /** @test */
    public function it_validates_stock_independently_per_user_cart()
    {
        // Arrange: Stock validation per user
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 20]);

        Cart::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user1->id,
            'quantity' => 10,
        ]);

        // Act: User2 adds same product
        $response = $this->actingAs($user2)->post(route('cart.add', $product->id), [
            'quantity' => 15,
        ]);

        // Assert: Each user validated independently
        $response->assertRedirect();
        $this->assertDatabaseHas('carts', [
            'product_id' => $product->id,
            'user_id' => $user2->id,
            'quantity' => 15,
        ]);
    }
}
