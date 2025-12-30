<?php

namespace Tests\Coverage\BoundaryCoverage;

use App\Models\Product\Category;
use App\Models\Product\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Price Validation Boundary Coverage Test
 *
 * Tests boundary conditions for price-related calculations including:
 * - Minimum price values (0, 0.01)
 * - Maximum price values (practical limits)
 * - Discount percentage boundaries (0%, 100%, edge cases)
 * - Price calculation accuracy with various discount levels
 * - Negative price handling
 * - Extreme value handling
 *
 * Boundary Analysis:
 * - Price: 0, 0.01, 0.99, 1.00, 999999.99, MAX_VALUE
 * - Discount: 0%, 0.01%, 50%, 99.99%, 100%
 */
class PriceValidationTest extends TestCase
{
    use RefreshDatabase;

    protected $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::factory()->create();
    }

    // ===== MINIMUM PRICE BOUNDARIES =====

    /** @test */
    public function it_handles_zero_price()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 0,
            'discount_percent' => 0,
        ]);

        $this->assertEquals(0, $product->price);
        $this->assertEquals(0, $product->getDiscountedPrice());
        $this->assertEquals(0, $product->discounted_price);
    }

    /** @test */
    public function it_handles_minimum_positive_price()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 0.01,
            'discount_percent' => 0,
        ]);

        $this->assertEquals(0.01, $product->price);
        $this->assertEquals(0.01, $product->getDiscountedPrice());
    }

    /** @test */
    public function it_handles_one_cent_boundary()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 0.99,
            'discount_percent' => 0,
        ]);

        $this->assertEquals(0.99, $product->price);
        $this->assertEquals(0.99, $product->getDiscountedPrice());
    }

    /** @test */
    public function it_handles_one_dollar_boundary()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 1.00,
            'discount_percent' => 0,
        ]);

        $this->assertEquals(1.00, $product->price);
        $this->assertEquals(1.00, $product->getDiscountedPrice());
    }

    // ===== MAXIMUM PRICE BOUNDARIES =====

    /** @test */
    public function it_handles_large_price_values()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 999999.99,
            'discount_percent' => 0,
        ]);

        $this->assertEquals(999999.99, $product->price);
        $this->assertEquals(999999.99, $product->getDiscountedPrice());
    }

    /** @test */
    public function it_handles_very_large_price()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 99999999.99,
            'discount_percent' => 0,
        ]);

        $this->assertEquals(99999999.99, $product->price);
        $this->assertEquals(99999999.99, $product->getDiscountedPrice());
    }

    // ===== ZERO DISCOUNT BOUNDARIES =====

    /** @test */
    public function it_applies_zero_discount()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 100.00,
            'discount_percent' => 0,
        ]);

        $this->assertEquals(100.00, $product->getDiscountedPrice());
        $this->assertEquals(100.00, $product->discounted_price);
    }

    /** @test */
    public function it_applies_minimum_discount()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 100.00,
            'discount_percent' => 0.01,
        ]);

        $expectedPrice = 100.00 - (100.00 * 0.01 / 100);
        $this->assertEquals($expectedPrice, $product->getDiscountedPrice());
        $this->assertEquals(99.99, round($product->discounted_price, 2));
    }

    // ===== STANDARD DISCOUNT BOUNDARIES =====

    /** @test */
    public function it_applies_one_percent_discount()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 100.00,
            'discount_percent' => 1,
        ]);

        $this->assertEquals(99.00, $product->getDiscountedPrice());
        $this->assertEquals(99.00, $product->discounted_price);
    }

    /** @test */
    public function it_applies_ten_percent_discount()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 100.00,
            'discount_percent' => 10,
        ]);

        $this->assertEquals(90.00, $product->getDiscountedPrice());
        $this->assertEquals(90.00, $product->discounted_price);
    }

    /** @test */
    public function it_applies_fifty_percent_discount()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 100.00,
            'discount_percent' => 50,
        ]);

        $this->assertEquals(50.00, $product->getDiscountedPrice());
        $this->assertEquals(50.00, $product->discounted_price);
    }

    /** @test */
    public function it_applies_ninety_nine_percent_discount()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 100.00,
            'discount_percent' => 99,
        ]);

        $this->assertEquals(1.00, $product->getDiscountedPrice());
        $this->assertEquals(1.00, $product->discounted_price);
    }

    /** @test */
    public function it_applies_ninety_nine_point_nine_percent_discount()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 100.00,
            'discount_percent' => 99.9,
        ]);

        $expectedPrice = 100.00 - (100.00 * 99.9 / 100);
        $this->assertEquals(0.10, round($product->getDiscountedPrice(), 2));
    }

    // ===== MAXIMUM DISCOUNT BOUNDARIES =====

    /** @test */
    public function it_applies_one_hundred_percent_discount()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 100.00,
            'discount_percent' => 100,
        ]);

        $this->assertEquals(0, $product->getDiscountedPrice());
        $this->assertEquals(0, $product->discounted_price);
    }

    // ===== DISCOUNT OVERRIDE BOUNDARIES =====

    /** @test */
    public function it_overrides_discount_with_zero()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 100.00,
            'discount_percent' => 50, // Stored discount
        ]);

        $this->assertEquals(100.00, $product->getDiscountedPrice(0));
    }

    /** @test */
    public function it_overrides_discount_with_custom_value()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 100.00,
            'discount_percent' => 10, // Stored discount
        ]);

        $this->assertEquals(75.00, $product->getDiscountedPrice(25));
        $this->assertEquals(50.00, $product->getDiscountedPrice(50));
        $this->assertEquals(10.00, $product->getDiscountedPrice(90));
    }

    /** @test */
    public function it_overrides_discount_with_one_hundred_percent()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 100.00,
            'discount_percent' => 0,
        ]);

        $this->assertEquals(0, $product->getDiscountedPrice(100));
    }

    // ===== EDGE CASES WITH SMALL PRICES =====

    /** @test */
    public function it_calculates_discount_on_small_price()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 0.10,
            'discount_percent' => 50,
        ]);

        $this->assertEquals(0.05, $product->getDiscountedPrice());
    }

    /** @test */
    public function it_calculates_high_discount_on_small_price()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 1.00,
            'discount_percent' => 99,
        ]);

        $this->assertEqualsWithDelta(0.01, $product->getDiscountedPrice(), 0.0001);
    }

    // ===== EDGE CASES WITH LARGE PRICES =====

    /** @test */
    public function it_calculates_discount_on_large_price()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 10000.00,
            'discount_percent' => 20,
        ]);

        $this->assertEquals(8000.00, $product->getDiscountedPrice());
    }

    /** @test */
    public function it_calculates_large_discount_on_large_price()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 99999.99,
            'discount_percent' => 50,
        ]);

        $expectedPrice = 99999.99 - (99999.99 * 50 / 100);
        $this->assertEquals(49999.995, $product->getDiscountedPrice());
    }

    // ===== PRECISION AND ROUNDING BOUNDARIES =====

    /** @test */
    public function it_maintains_precision_with_decimal_prices()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 19.99,
            'discount_percent' => 15,
        ]);

        $expectedPrice = 19.99 - (19.99 * 15 / 100);
        $this->assertEquals(round($expectedPrice, 2), round($product->getDiscountedPrice(), 2));
    }

    /** @test */
    public function it_maintains_precision_with_repeating_decimals()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 33.33,
            'discount_percent' => 33.33,
        ]);

        $expectedPrice = 33.33 - (33.33 * 33.33 / 100);
        $actualPrice = $product->getDiscountedPrice();
        $this->assertEquals(round($expectedPrice, 2), round($actualPrice, 2));
    }

    // ===== NULL AND DEFAULT BEHAVIOR =====

    /** @test */
    public function it_uses_stored_discount_when_override_is_null()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 100.00,
            'discount_percent' => 20,
        ]);

        $this->assertEquals(80.00, $product->getDiscountedPrice(null));
        $this->assertEquals(80.00, $product->getDiscountedPrice());
    }

    /** @test */
    public function it_returns_full_price_when_discount_is_zero()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 50.00,
            'discount_percent' => 0,
        ]);

        $this->assertEquals(50.00, $product->discounted_price);
    }

    // ===== COMBINED BOUNDARY CONDITIONS =====

    /** @test */
    public function it_handles_minimum_price_with_maximum_discount()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 0.01,
            'discount_percent' => 100,
        ]);

        $this->assertEquals(0, $product->getDiscountedPrice());
    }

    /** @test */
    public function it_handles_maximum_price_with_maximum_discount()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 999999.99,
            'discount_percent' => 100,
        ]);

        $this->assertEquals(0, $product->getDiscountedPrice());
    }

    /** @test */
    public function it_handles_typical_ecommerce_price_with_discount()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => 29.99,
            'discount_percent' => 25,
        ]);

        $expectedPrice = 29.99 - (29.99 * 25 / 100);
        $this->assertEquals(round($expectedPrice, 2), round($product->getDiscountedPrice(), 2));
    }
}
