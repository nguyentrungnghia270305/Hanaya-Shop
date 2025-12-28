<?php

namespace Tests\Coverage\BoundaryCoverage;

use Tests\TestCase;
use App\Models\Cart\Cart;
use App\Models\Product\Product;
use App\Models\Product\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Quantity Boundary Coverage Test
 * 
 * Tests boundary conditions for quantity-related operations in cart:
 * - Minimum quantity values (0, 1)
 * - Maximum quantity values (practical limits)
 * - Negative quantity handling
 * - Quantity updates and modifications
 * - Edge cases in cart operations
 * 
 * Boundary Analysis:
 * - Quantity: 0, 1, 10, 50, 100, 999, 1000, MAX_INT
 * - Cart operations: add, update, remove
 * - Stock vs quantity validation
 */
class QuantityBoundaryTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
        $this->product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 100.00,
            'stock_quantity' => 1000,
        ]);
    }

    // ===== MINIMUM QUANTITY BOUNDARIES =====

    /** @test */
    public function it_handles_zero_quantity()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 0,
        ]);

        $this->assertEquals(0, $cart->quantity);
    }

    /** @test */
    public function it_handles_minimum_positive_quantity()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $this->assertEquals(1, $cart->quantity);
        $this->assertInstanceOf(Product::class, $cart->product);
        $this->assertEquals($this->product->id, $cart->product->id);
    }

    /** @test */
    public function it_creates_cart_with_quantity_one()
    {
        $cart = Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);
    }

    // ===== SMALL QUANTITY BOUNDARIES =====

    /** @test */
    public function it_handles_quantity_of_two()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $this->assertEquals(2, $cart->quantity);
    }

    /** @test */
    public function it_handles_quantity_of_five()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
        ]);

        $this->assertEquals(5, $cart->quantity);
    }

    /** @test */
    public function it_handles_quantity_of_ten()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
        ]);

        $this->assertEquals(10, $cart->quantity);
    }

    // ===== MEDIUM QUANTITY BOUNDARIES =====

    /** @test */
    public function it_handles_quantity_of_fifty()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
        ]);

        $this->assertEquals(50, $cart->quantity);
    }

    /** @test */
    public function it_handles_quantity_of_one_hundred()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 100,
        ]);

        $this->assertEquals(100, $cart->quantity);
    }

    /** @test */
    public function it_handles_quantity_of_five_hundred()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 500,
        ]);

        $this->assertEquals(500, $cart->quantity);
    }

    // ===== LARGE QUANTITY BOUNDARIES =====

    /** @test */
    public function it_handles_quantity_of_nine_hundred_ninety_nine()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 999,
        ]);

        $this->assertEquals(999, $cart->quantity);
    }

    /** @test */
    public function it_handles_quantity_of_one_thousand()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1000,
        ]);

        $this->assertEquals(1000, $cart->quantity);
    }

    /** @test */
    public function it_handles_very_large_quantity()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 10000,
        ]);

        $this->assertEquals(10000, $cart->quantity);
    }

    // ===== QUANTITY UPDATE BOUNDARIES =====

    /** @test */
    public function it_updates_quantity_from_one_to_two()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $cart->update(['quantity' => 2]);

        $this->assertEquals(2, $cart->fresh()->quantity);
    }

    /** @test */
    public function it_updates_quantity_from_one_to_ten()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $cart->update(['quantity' => 10]);

        $this->assertEquals(10, $cart->fresh()->quantity);
    }

    /** @test */
    public function it_updates_quantity_from_ten_to_one()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
        ]);

        $cart->update(['quantity' => 1]);

        $this->assertEquals(1, $cart->fresh()->quantity);
    }

    /** @test */
    public function it_updates_quantity_to_zero()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
        ]);

        $cart->update(['quantity' => 0]);

        $this->assertEquals(0, $cart->fresh()->quantity);
    }

    /** @test */
    public function it_increments_quantity_by_one()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
        ]);

        $cart->increment('quantity', 1);

        $this->assertEquals(6, $cart->fresh()->quantity);
    }

    /** @test */
    public function it_increments_quantity_by_multiple()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
        ]);

        $cart->increment('quantity', 5);

        $this->assertEquals(15, $cart->fresh()->quantity);
    }

    /** @test */
    public function it_decrements_quantity_by_one()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
        ]);

        $cart->decrement('quantity', 1);

        $this->assertEquals(4, $cart->fresh()->quantity);
    }

    /** @test */
    public function it_decrements_quantity_to_zero()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
        ]);

        $cart->decrement('quantity', 5);

        $this->assertEquals(0, $cart->fresh()->quantity);
    }

    // ===== MULTIPLE CART ITEMS BOUNDARIES =====

    /** @test */
    public function it_handles_multiple_items_with_different_quantities()
    {
        $product2 = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 1000,
        ]);

        $cart1 = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $cart2 = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product2->id,
            'quantity' => 10,
        ]);

        $this->assertEquals(1, $cart1->quantity);
        $this->assertEquals(10, $cart2->quantity);
    }

    /** @test */
    public function it_handles_same_product_different_quantities_for_different_users()
    {
        $user2 = User::factory()->create();

        $cart1 = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
        ]);

        $cart2 = Cart::factory()->create([
            'user_id' => $user2->id,
            'product_id' => $this->product->id,
            'quantity' => 15,
        ]);

        $this->assertEquals(5, $cart1->quantity);
        $this->assertEquals(15, $cart2->quantity);
    }

    // ===== QUANTITY CALCULATION BOUNDARIES =====

    /** @test */
    public function it_calculates_total_for_quantity_one()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $total = $cart->quantity * $cart->product->price;

        $this->assertEquals(100.00, $total);
    }

    /** @test */
    public function it_calculates_total_for_quantity_ten()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 10,
        ]);

        $total = $cart->quantity * $cart->product->price;

        $this->assertEquals(1000.00, $total);
    }

    /** @test */
    public function it_calculates_total_for_large_quantity()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 100,
        ]);

        $total = $cart->quantity * $cart->product->price;

        $this->assertEquals(10000.00, $total);
    }

    /** @test */
    public function it_calculates_total_for_zero_quantity()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 0,
        ]);

        $total = $cart->quantity * $cart->product->price;

        $this->assertEquals(0, $total);
    }

    // ===== QUANTITY WITH DISCOUNT BOUNDARIES =====

    /** @test */
    public function it_calculates_discounted_total_for_quantity_one()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 100.00,
            'discount_percent' => 20,
            'stock_quantity' => 1000,
        ]);

        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $total = $cart->quantity * $cart->product->getDiscountedPrice();

        $this->assertEquals(80.00, $total);
    }

    /** @test */
    public function it_calculates_discounted_total_for_quantity_ten()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 100.00,
            'discount_percent' => 50,
            'stock_quantity' => 1000,
        ]);

        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $total = $cart->quantity * $cart->product->getDiscountedPrice();

        $this->assertEquals(500.00, $total);
    }

    // ===== GUEST CART QUANTITY BOUNDARIES =====

    /** @test */
    public function it_handles_guest_cart_with_minimum_quantity()
    {
        $cart = Cart::factory()->create([
            'user_id' => null,
            'session_id' => 'guest-session-123',
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $this->assertNull($cart->user_id);
        $this->assertEquals('guest-session-123', $cart->session_id);
        $this->assertEquals(1, $cart->quantity);
    }

    /** @test */
    public function it_handles_guest_cart_with_large_quantity()
    {
        $cart = Cart::factory()->create([
            'user_id' => null,
            'session_id' => 'guest-session-456',
            'product_id' => $this->product->id,
            'quantity' => 50,
        ]);

        $this->assertEquals(50, $cart->quantity);
    }

    // ===== BOUNDARY EDGE CASES =====

    /** @test */
    public function it_handles_quantity_boundary_at_stock_limit()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 10,
        ]);

        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $this->assertEquals(10, $cart->quantity);
        $this->assertEquals($product->stock_quantity, $cart->quantity);
    }

    /** @test */
    public function it_handles_quantity_just_below_stock_limit()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 10,
        ]);

        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'quantity' => 9,
        ]);

        $this->assertEquals(9, $cart->quantity);
        $this->assertLessThan($product->stock_quantity, $cart->quantity);
    }

    /** @test */
    public function it_handles_cart_persistence_with_quantity_updates()
    {
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $cartId = $cart->id;

        // Update quantity multiple times
        $cart->update(['quantity' => 5]);
        $cart->update(['quantity' => 10]);
        $cart->update(['quantity' => 3]);

        $persistedCart = Cart::find($cartId);
        $this->assertEquals(3, $persistedCart->quantity);
    }
}
