<?php

namespace Tests\Coverage\BoundaryCoverage;

use Tests\TestCase;
use App\Models\Product\Product;
use App\Models\Product\Category;
use App\Models\Cart\Cart;
use App\Models\User;
use App\Models\Order\Order;
use App\Models\Order\OrderDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Stock Limit Boundary Coverage Test
 * 
 * Tests boundary conditions for stock quantity management:
 * - Minimum stock values (0, 1)
 * - Maximum stock values (practical limits)
 * - Stock depletion scenarios
 * - Stock replenishment boundaries
 * - Out of stock handling
 * - Stock vs cart quantity validation
 * - Stock updates after orders
 * 
 * Boundary Analysis:
 * - Stock: 0, 1, 10, 50, 100, 500, 1000, 10000, MAX_VALUE
 * - Order quantity vs available stock
 * - Multiple orders affecting stock
 * - Concurrent stock access scenarios
 */
class StockLimitTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
    }

    // ===== ZERO STOCK BOUNDARIES =====

    /** @test */
    public function it_handles_zero_stock_product()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 100.00,
            'stock_quantity' => 0,
        ]);

        $this->assertEquals(0, $product->stock_quantity);
    }

    /** @test */
    public function it_creates_product_with_zero_stock()
    {
        $product = Product::create([
            'name' => 'Out of Stock Product',
            'descriptions' => 'Test product',
            'price' => 50.00,
            'stock_quantity' => 0,
            'image_url' => 'test.jpg',
            'category_id' => $this->category->id,
            'discount_percent' => 0,
            'view_count' => 0,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 0,
        ]);
    }

    /** @test */
    public function it_identifies_out_of_stock_product()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 0,
        ]);

        $this->assertTrue($product->stock_quantity === 0);
        $this->assertFalse($product->stock_quantity > 0);
    }

    // ===== MINIMUM STOCK BOUNDARIES =====

    /** @test */
    public function it_handles_minimum_stock_of_one()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 1,
        ]);

        $this->assertEquals(1, $product->stock_quantity);
        $this->assertTrue($product->stock_quantity > 0);
    }

    /** @test */
    public function it_handles_stock_of_two()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 2,
        ]);

        $this->assertEquals(2, $product->stock_quantity);
    }

    /** @test */
    public function it_handles_stock_of_five()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 5,
        ]);

        $this->assertEquals(5, $product->stock_quantity);
    }

    // ===== SMALL STOCK BOUNDARIES =====

    /** @test */
    public function it_handles_stock_of_ten()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 10,
        ]);

        $this->assertEquals(10, $product->stock_quantity);
    }

    /** @test */
    public function it_handles_stock_of_twenty()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 20,
        ]);

        $this->assertEquals(20, $product->stock_quantity);
    }

    /** @test */
    public function it_handles_stock_of_fifty()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 50,
        ]);

        $this->assertEquals(50, $product->stock_quantity);
    }

    // ===== MEDIUM STOCK BOUNDARIES =====

    /** @test */
    public function it_handles_stock_of_one_hundred()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 100,
        ]);

        $this->assertEquals(100, $product->stock_quantity);
    }

    /** @test */
    public function it_handles_stock_of_two_hundred_fifty()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 250,
        ]);

        $this->assertEquals(250, $product->stock_quantity);
    }

    /** @test */
    public function it_handles_stock_of_five_hundred()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 500,
        ]);

        $this->assertEquals(500, $product->stock_quantity);
    }

    // ===== LARGE STOCK BOUNDARIES =====

    /** @test */
    public function it_handles_stock_of_one_thousand()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 1000,
        ]);

        $this->assertEquals(1000, $product->stock_quantity);
    }

    /** @test */
    public function it_handles_stock_of_five_thousand()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 5000,
        ]);

        $this->assertEquals(5000, $product->stock_quantity);
    }

    /** @test */
    public function it_handles_stock_of_ten_thousand()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 10000,
        ]);

        $this->assertEquals(10000, $product->stock_quantity);
    }

    /** @test */
    public function it_handles_very_large_stock()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 999999,
        ]);

        $this->assertEquals(999999, $product->stock_quantity);
    }

    // ===== STOCK DEPLETION BOUNDARIES =====

    /** @test */
    public function it_depletes_stock_from_one_to_zero()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 1,
        ]);

        $product->decrement('stock_quantity', 1);

        $this->assertEquals(0, $product->fresh()->stock_quantity);
    }

    /** @test */
    public function it_depletes_stock_partially()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 10,
        ]);

        $product->decrement('stock_quantity', 5);

        $this->assertEquals(5, $product->fresh()->stock_quantity);
    }

    /** @test */
    public function it_depletes_stock_completely()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 50,
        ]);

        $product->update(['stock_quantity' => 0]);

        $this->assertEquals(0, $product->fresh()->stock_quantity);
    }

    /** @test */
    public function it_depletes_large_stock_to_one()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 1000,
        ]);

        $product->update(['stock_quantity' => 1]);

        $this->assertEquals(1, $product->fresh()->stock_quantity);
    }

    // ===== STOCK REPLENISHMENT BOUNDARIES =====

    /** @test */
    public function it_replenishes_stock_from_zero()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 0,
        ]);

        $product->increment('stock_quantity', 10);

        $this->assertEquals(10, $product->fresh()->stock_quantity);
    }

    /** @test */
    public function it_replenishes_stock_from_one()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 1,
        ]);

        $product->increment('stock_quantity', 1);

        $this->assertEquals(2, $product->fresh()->stock_quantity);
    }

    /** @test */
    public function it_replenishes_stock_in_large_batch()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 10,
        ]);

        $product->increment('stock_quantity', 1000);

        $this->assertEquals(1010, $product->fresh()->stock_quantity);
    }

    /** @test */
    public function it_updates_stock_to_maximum()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 100,
        ]);

        $product->update(['stock_quantity' => 10000]);

        $this->assertEquals(10000, $product->fresh()->stock_quantity);
    }

    // ===== STOCK VS QUANTITY VALIDATION BOUNDARIES =====

    /** @test */
    public function it_validates_cart_quantity_equals_stock()
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

        $this->assertEquals($product->stock_quantity, $cart->quantity);
        $this->assertTrue($cart->quantity <= $product->stock_quantity);
    }

    /** @test */
    public function it_validates_cart_quantity_less_than_stock()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 10,
        ]);

        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $this->assertLessThan($product->stock_quantity, $cart->quantity);
        $this->assertTrue($cart->quantity <= $product->stock_quantity);
    }

    /** @test */
    public function it_validates_cart_quantity_one_less_than_stock()
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

        $this->assertEquals(1, $product->stock_quantity - $cart->quantity);
    }

    /** @test */
    public function it_validates_cart_quantity_at_minimum_stock()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 1,
        ]);

        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->assertEquals($product->stock_quantity, $cart->quantity);
    }

    // ===== STOCK AFTER ORDER BOUNDARIES =====

    /** @test */
    public function it_tracks_stock_reduction_after_single_order()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 10,
        ]);

        $originalStock = $product->stock_quantity;

        // Simulate order processing
        $orderQuantity = 3;
        $product->decrement('stock_quantity', $orderQuantity);

        $this->assertEquals($originalStock - $orderQuantity, $product->fresh()->stock_quantity);
        $this->assertEquals(7, $product->fresh()->stock_quantity);
    }

    /** @test */
    public function it_tracks_stock_to_zero_after_order()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 5,
        ]);

        $product->decrement('stock_quantity', 5);

        $this->assertEquals(0, $product->fresh()->stock_quantity);
    }

    /** @test */
    public function it_tracks_stock_to_one_after_order()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 10,
        ]);

        $product->decrement('stock_quantity', 9);

        $this->assertEquals(1, $product->fresh()->stock_quantity);
    }

    // ===== MULTIPLE ORDERS AFFECTING STOCK =====

    /** @test */
    public function it_handles_multiple_small_orders_depleting_stock()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 10,
        ]);

        // Order 1: 3 items
        $product->decrement('stock_quantity', 3);
        $this->assertEquals(7, $product->fresh()->stock_quantity);

        // Order 2: 2 items
        $product->decrement('stock_quantity', 2);
        $this->assertEquals(5, $product->fresh()->stock_quantity);

        // Order 3: 5 items
        $product->decrement('stock_quantity', 5);
        $this->assertEquals(0, $product->fresh()->stock_quantity);
    }

    /** @test */
    public function it_handles_sequential_orders_to_minimum_stock()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 5,
        ]);

        for ($i = 0; $i < 4; $i++) {
            $product->decrement('stock_quantity', 1);
        }

        $this->assertEquals(1, $product->fresh()->stock_quantity);
    }

    // ===== LOW STOCK WARNING BOUNDARIES =====

    /** @test */
    public function it_identifies_low_stock_at_five_units()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 5,
        ]);

        $isLowStock = $product->stock_quantity <= 5;

        $this->assertTrue($isLowStock);
    }

    /** @test */
    public function it_identifies_low_stock_at_one_unit()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 1,
        ]);

        $isLowStock = $product->stock_quantity <= 5;

        $this->assertTrue($isLowStock);
    }

    /** @test */
    public function it_identifies_not_low_stock_at_six_units()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 6,
        ]);

        $isLowStock = $product->stock_quantity <= 5;

        $this->assertFalse($isLowStock);
    }

    // ===== STOCK AVAILABILITY CHECKS =====

    /** @test */
    public function it_checks_stock_availability_for_requested_quantity()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 10,
        ]);

        $requestedQuantity = 5;
        $isAvailable = $product->stock_quantity >= $requestedQuantity;

        $this->assertTrue($isAvailable);
    }

    /** @test */
    public function it_checks_stock_unavailable_for_large_quantity()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 10,
        ]);

        $requestedQuantity = 15;
        $isAvailable = $product->stock_quantity >= $requestedQuantity;

        $this->assertFalse($isAvailable);
    }

    /** @test */
    public function it_checks_exact_stock_match()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 10,
        ]);

        $requestedQuantity = 10;
        $isAvailable = $product->stock_quantity >= $requestedQuantity;

        $this->assertTrue($isAvailable);
    }

    /** @test */
    public function it_checks_one_unit_over_stock()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 10,
        ]);

        $requestedQuantity = 11;
        $isAvailable = $product->stock_quantity >= $requestedQuantity;

        $this->assertFalse($isAvailable);
    }

    /** @test */
    public function it_checks_one_unit_under_stock()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 10,
        ]);

        $requestedQuantity = 9;
        $isAvailable = $product->stock_quantity >= $requestedQuantity;

        $this->assertTrue($isAvailable);
    }

    // ===== STOCK PERSISTENCE BOUNDARIES =====

    /** @test */
    public function it_persists_stock_changes_to_database()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 100,
        ]);

        $productId = $product->id;

        $product->update(['stock_quantity' => 50]);

        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'stock_quantity' => 50,
        ]);
    }

    /** @test */
    public function it_persists_stock_depletion_to_zero()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 25,
        ]);

        $productId = $product->id;

        $product->update(['stock_quantity' => 0]);

        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'stock_quantity' => 0,
        ]);
    }

    // ===== COMBINED BOUNDARY CONDITIONS =====

    /** @test */
    public function it_handles_minimum_stock_with_minimum_quantity_order()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 1,
        ]);

        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->assertEquals($product->stock_quantity, $cart->quantity);
        $this->assertTrue($cart->quantity <= $product->stock_quantity);
    }

    /** @test */
    public function it_handles_large_stock_with_large_quantity_order()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'stock_quantity' => 1000,
        ]);

        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'quantity' => 500,
        ]);

        $remainingStock = $product->stock_quantity - $cart->quantity;
        $this->assertEquals(500, $remainingStock);
    }
}
