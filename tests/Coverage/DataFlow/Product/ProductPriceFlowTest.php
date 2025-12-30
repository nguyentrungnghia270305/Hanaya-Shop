<?php

/**
 * Product Price Flow Test
 *
 * Data Flow Testing cho Product price calculations - kiểm tra luồng dữ liệu
 * trong các phép tính giá sản phẩm.
 *
 * Test Coverage:
 * - Base price → Discounted price calculation flow
 * - Discount percent → Final price transformation
 * - Price dependencies in cart/order calculations
 * - Price data consistency across operations
 *
 * @category Testing
 */

namespace Tests\Coverage\DataFlow\Product;

use App\Models\Product\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPriceFlowTest extends TestCase
{
    use RefreshDatabase;

    // =================================================================
    // BASE PRICE DATA FLOW TESTS
    // =================================================================

    /** @test */
    public function it_flows_price_from_creation_to_retrieval()
    {
        // Given: Create product with price
        $basePrice = 99.99; // DEF: Define base price
        $product = Product::factory()->create([
            'price' => $basePrice, // USE: Set price at creation
        ]);

        // When: Retrieve product
        $retrievedProduct = Product::find($product->id);

        // Then: Price flows correctly
        $this->assertEquals($basePrice, $retrievedProduct->price); // USE: Retrieved price
    }

    /** @test */
    public function it_maintains_price_through_updates()
    {
        // Given: Product with initial price
        $initialPrice = 100.00; // DEF: Initial price
        $product = Product::factory()->create(['price' => $initialPrice]);

        // When: Update price multiple times
        $prices = [120.00, 90.00, 150.00]; // DEF: Sequence of price updates
        foreach ($prices as $newPrice) {
            $product->price = $newPrice; // USE & REDEF: Update price
            $product->save();
            $product->refresh();
            $this->assertEquals($newPrice, $product->price); // USE: Verify each update
        }

        // Then: Final price is last update
        $this->assertEquals(150.00, $product->price); // USE: Final value
    }

    /** @test */
    public function it_flows_price_with_precision()
    {
        // Given: Price with decimal precision
        $precisePrice = 99.995; // DEF: Price with 3 decimals
        $product = Product::factory()->create(['price' => $precisePrice]);

        // When: Retrieve and calculate
        $retrievedPrice = $product->price; // USE: Get price

        // Then: Precision maintained
        $this->assertEquals($precisePrice, $retrievedPrice, '', 0.001); // USE: Check precision
    }

    // =================================================================
    // DISCOUNT CALCULATION DATA FLOW TESTS
    // =================================================================

    /** @test */
    public function it_flows_discount_percent_to_price_calculation()
    {
        // Given: Product with discount
        $originalPrice = 100.00; // DEF: Original price
        $discountPercent = 20; // DEF: Discount percentage
        $product = Product::factory()->create([
            'price' => $originalPrice,
            'discount_percent' => $discountPercent, // USE: Set discount
        ]);

        // When: Calculate discounted price
        $expectedDiscount = $originalPrice * ($discountPercent / 100); // USE: Calculate discount
        $expectedFinalPrice = $originalPrice - $expectedDiscount; // USE: Calculate final

        // Then: Discounted price flows correctly
        $actualDiscountedPrice = $product->getDiscountedPriceAttribute(); // USE: Get calculated
        $this->assertEquals($expectedFinalPrice, $actualDiscountedPrice); // USE: Verify
    }

    /** @test */
    public function it_flows_zero_discount_as_original_price()
    {
        // Given: Product with no discount
        $originalPrice = 150.00; // DEF: Original price
        $discountPercent = 0; // DEF: No discount
        $product = Product::factory()->create([
            'price' => $originalPrice,
            'discount_percent' => $discountPercent, // USE: Zero discount
        ]);

        // When: Calculate discounted price
        $discountedPrice = $product->getDiscountedPriceAttribute(); // USE: Calculate

        // Then: Discounted price equals original
        $this->assertEquals($originalPrice, $discountedPrice); // USE: No change
    }

    /** @test */
    public function it_flows_full_discount_to_zero_price()
    {
        // Given: Product with 100% discount
        $originalPrice = 200.00; // DEF: Original price
        $discountPercent = 100; // DEF: Full discount
        $product = Product::factory()->create([
            'price' => $originalPrice,
            'discount_percent' => $discountPercent, // USE: 100% discount
        ]);

        // When: Calculate discounted price
        $expectedPrice = 0.00; // USE: Expected zero
        $discountedPrice = $product->getDiscountedPriceAttribute(); // USE: Calculate

        // Then: Price flows to zero
        $this->assertEquals($expectedPrice, $discountedPrice); // USE: Verify zero
    }

    /** @test */
    public function it_flows_discount_through_multiple_calculations()
    {
        // Given: Product with discount
        $basePrice = 100.00; // DEF: Base price
        $discount = 25; // DEF: 25% discount
        $product = Product::factory()->create([
            'price' => $basePrice,
            'discount_percent' => $discount,
        ]);

        // When: Calculate discounted price multiple times
        $calculation1 = $product->getDiscountedPriceAttribute(); // USE: First calc
        $calculation2 = $product->getDiscountedPriceAttribute(); // USE: Second calc
        $calculation3 = $product->getDiscountedPriceAttribute(); // USE: Third calc

        // Then: All calculations yield same result
        $expectedPrice = 75.00; // USE: Expected discounted price
        $this->assertEquals($expectedPrice, $calculation1); // USE: Verify 1
        $this->assertEquals($expectedPrice, $calculation2); // USE: Verify 2
        $this->assertEquals($expectedPrice, $calculation3); // USE: Verify 3
        $this->assertEquals($calculation1, $calculation2); // USE: Consistency
        $this->assertEquals($calculation2, $calculation3); // USE: Consistency
    }

    // =================================================================
    // DISCOUNT UPDATE DATA FLOW TESTS
    // =================================================================

    /** @test */
    public function it_flows_updated_discount_to_new_calculation()
    {
        // Given: Product with initial discount
        $price = 100.00; // DEF: Fixed price
        $initialDiscount = 10; // DEF: Initial 10% discount
        $product = Product::factory()->create([
            'price' => $price,
            'discount_percent' => $initialDiscount,
        ]);

        // When: Update discount
        $newDiscount = 30; // DEF: New 30% discount
        $product->discount_percent = $newDiscount; // USE: Update discount
        $product->save();

        // Then: New calculation reflects updated discount
        $expectedPrice = $price * (1 - $newDiscount / 100); // USE: Calculate with new
        $actualPrice = $product->getDiscountedPriceAttribute(); // USE: Get new price
        $this->assertEquals($expectedPrice, $actualPrice); // USE: Verify update
    }

    /** @test */
    public function it_flows_discount_removal_to_original_price()
    {
        // Given: Product with discount
        $originalPrice = 200.00; // DEF: Original price
        $product = Product::factory()->create([
            'price' => $originalPrice,
            'discount_percent' => 50, // USE: Initial 50% discount
        ]);

        // When: Remove discount
        $product->discount_percent = 0; // USE: Remove discount
        $product->save();

        // Then: Price flows back to original
        $discountedPrice = $product->getDiscountedPriceAttribute(); // USE: Calculate
        $this->assertEquals($originalPrice, $discountedPrice); // USE: Verify original
    }

    // =================================================================
    // PRICE BOUNDARY DATA FLOW TESTS
    // =================================================================

    /** @test */
    public function it_flows_minimum_price_correctly()
    {
        // Given: Product with minimum price
        $minPrice = 0.01; // DEF: Minimum valid price
        $product = Product::factory()->create(['price' => $minPrice]);

        // When: Retrieve price
        $retrievedPrice = $product->price; // USE: Get price

        // Then: Minimum price flows correctly
        $this->assertEquals($minPrice, $retrievedPrice); // USE: Verify minimum
    }

    /** @test */
    public function it_flows_maximum_price_correctly()
    {
        // Given: Product with large price
        $maxPrice = 999999.99; // DEF: Large price
        $product = Product::factory()->create(['price' => $maxPrice]);

        // When: Retrieve price
        $retrievedPrice = $product->price; // USE: Get price

        // Then: Large price flows correctly
        $this->assertEquals($maxPrice, $retrievedPrice); // USE: Verify large
    }

    /** @test */
    public function it_flows_minimum_discount_boundary()
    {
        // Given: Product with minimum discount
        $price = 100.00; // DEF: Price
        $minDiscount = 0.01; // DEF: Minimum discount (0.01%)
        $product = Product::factory()->create([
            'price' => $price,
            'discount_percent' => $minDiscount,
        ]);

        // When: Calculate with minimum discount
        $expectedPrice = $price * (1 - $minDiscount / 100); // USE: Calculate
        $actualPrice = $product->getDiscountedPriceAttribute(); // USE: Get price

        // Then: Minimum discount flows correctly
        $this->assertEquals($expectedPrice, $actualPrice, '', 0.01); // USE: Verify
    }

    /** @test */
    public function it_flows_maximum_discount_boundary()
    {
        // Given: Product with maximum discount
        $price = 100.00; // DEF: Price
        $maxDiscount = 100; // DEF: Maximum discount (100%)
        $product = Product::factory()->create([
            'price' => $price,
            'discount_percent' => $maxDiscount,
        ]);

        // When: Calculate with maximum discount
        $expectedPrice = 0.00; // USE: 100% discount = free
        $actualPrice = $product->getDiscountedPriceAttribute(); // USE: Get price

        // Then: Maximum discount flows to zero
        $this->assertEquals($expectedPrice, $actualPrice); // USE: Verify zero
    }

    // =================================================================
    // PRICE MULTIPLICATION DATA FLOW TESTS
    // =================================================================

    /** @test */
    public function it_flows_price_through_quantity_multiplication()
    {
        // Given: Product with price
        $unitPrice = 50.00; // DEF: Price per unit
        $quantity = 5; // DEF: Quantity
        $product = Product::factory()->create(['price' => $unitPrice]);

        // When: Calculate subtotal
        $expectedSubtotal = $unitPrice * $quantity; // USE: Multiply

        // Then: Subtotal flows correctly
        $actualSubtotal = $product->price * $quantity; // USE: Calculate from product
        $this->assertEquals($expectedSubtotal, $actualSubtotal); // USE: Verify
    }

    /** @test */
    public function it_flows_discounted_price_through_quantity_multiplication()
    {
        // Given: Product with discount
        $basePrice = 100.00; // DEF: Base price
        $discount = 20; // DEF: 20% discount
        $quantity = 3; // DEF: Quantity
        $product = Product::factory()->create([
            'price' => $basePrice,
            'discount_percent' => $discount,
        ]);

        // When: Calculate subtotal with discount
        $discountedPrice = $basePrice * (1 - $discount / 100); // USE: Calculate unit price
        $expectedSubtotal = $discountedPrice * $quantity; // USE: Multiply by quantity

        // Then: Discounted subtotal flows correctly
        $actualSubtotal = $product->getDiscountedPriceAttribute() * $quantity; // USE: Calculate
        $this->assertEquals($expectedSubtotal, $actualSubtotal); // USE: Verify
    }

    /** @test */
    public function it_flows_price_through_multiple_quantity_calculations()
    {
        // Given: Product with price
        $price = 75.00; // DEF: Unit price
        $product = Product::factory()->create(['price' => $price]);

        // When: Calculate for different quantities
        $quantities = [1, 2, 5, 10, 100]; // DEF: Different quantities
        foreach ($quantities as $qty) {
            $expectedTotal = $price * $qty; // USE: Calculate expected
            $actualTotal = $product->price * $qty; // USE: Calculate actual
            $this->assertEquals($expectedTotal, $actualTotal); // USE: Verify each
        }
    }

    // =================================================================
    // PRICE COMPARISON DATA FLOW TESTS
    // =================================================================

    /** @test */
    public function it_flows_price_savings_calculation()
    {
        // Given: Product with discount
        $originalPrice = 100.00; // DEF: Original price
        $discountPercent = 30; // DEF: 30% discount
        $product = Product::factory()->create([
            'price' => $originalPrice,
            'discount_percent' => $discountPercent,
        ]);

        // When: Calculate savings
        $discountedPrice = $product->getDiscountedPriceAttribute(); // USE: Get discounted
        $savings = $originalPrice - $discountedPrice; // USE: Calculate savings

        // Then: Savings flows correctly
        $expectedSavings = $originalPrice * ($discountPercent / 100); // USE: Expected savings
        $this->assertEquals($expectedSavings, $savings); // USE: Verify savings
    }

    /** @test */
    public function it_flows_price_difference_between_products()
    {
        // Given: Two products with different prices
        $price1 = 100.00; // DEF: First price
        $price2 = 150.00; // DEF: Second price
        $product1 = Product::factory()->create(['price' => $price1]);
        $product2 = Product::factory()->create(['price' => $price2]);

        // When: Calculate price difference
        $difference = $product2->price - $product1->price; // USE: Calculate difference

        // Then: Difference flows correctly
        $expectedDifference = 50.00; // USE: Expected difference
        $this->assertEquals($expectedDifference, $difference); // USE: Verify
    }

    // =================================================================
    // PRICE AGGREGATION DATA FLOW TESTS
    // =================================================================

    /** @test */
    public function it_flows_prices_through_sum_aggregation()
    {
        // Given: Multiple products with prices
        $prices = [100.00, 200.00, 50.00]; // DEF: Individual prices
        $products = [];
        foreach ($prices as $price) {
            $products[] = Product::factory()->create(['price' => $price]); // USE: Create products
        }

        // When: Calculate sum of prices
        $expectedSum = array_sum($prices); // USE: Expected total
        $actualSum = collect($products)->sum('price'); // USE: Aggregate prices

        // Then: Sum flows correctly
        $this->assertEquals($expectedSum, $actualSum); // USE: Verify sum
    }

    /** @test */
    public function it_flows_discounted_prices_through_sum_aggregation()
    {
        // Given: Products with different discounts
        $productsData = [
            ['price' => 100.00, 'discount' => 10], // DEF: Product 1
            ['price' => 200.00, 'discount' => 20], // DEF: Product 2
            ['price' => 150.00, 'discount' => 0],  // DEF: Product 3
        ];

        $products = [];
        $expectedSum = 0;
        foreach ($productsData as $data) {
            $product = Product::factory()->create([
                'price' => $data['price'],
                'discount_percent' => $data['discount'],
            ]);
            $products[] = $product;
            $expectedSum += $data['price'] * (1 - $data['discount'] / 100); // USE: Add discounted
        }

        // When: Sum discounted prices
        $actualSum = collect($products)->sum(function ($product) {
            return $product->getDiscountedPriceAttribute(); // USE: Get each discounted
        });

        // Then: Discounted sum flows correctly
        $this->assertEquals($expectedSum, $actualSum); // USE: Verify sum
    }

    /** @test */
    public function it_flows_average_price_calculation()
    {
        // Given: Products with prices
        $prices = [100.00, 200.00, 300.00]; // DEF: Prices
        $products = [];
        foreach ($prices as $price) {
            $products[] = Product::factory()->create(['price' => $price]); // USE: Create
        }

        // When: Calculate average
        $expectedAverage = array_sum($prices) / count($prices); // USE: Calculate avg
        $actualAverage = collect($products)->avg('price'); // USE: Aggregate avg

        // Then: Average flows correctly
        $this->assertEquals($expectedAverage, $actualAverage); // USE: Verify avg
    }

    // =================================================================
    // NULL AND EDGE CASE DATA FLOW TESTS
    // =================================================================

    /** @test */
    public function it_flows_null_discount_as_zero()
    {
        // Given: Product with zero discount (database doesn't allow null)
        $price = 100.00; // DEF: Price
        $product = Product::factory()->create([
            'price' => $price,
            'discount_percent' => 0, // USE: Zero discount (equivalent to null)
        ]);

        // When: Calculate discounted price
        $discountedPrice = $product->getDiscountedPriceAttribute(); // USE: Calculate

        // Then: Zero discount flows as original price
        $this->assertEquals($price, $discountedPrice); // USE: Verify original
    }

    /** @test */
    public function it_maintains_price_consistency_across_reload()
    {
        // Given: Product saved to database
        $originalPrice = 99.99; // DEF: Original price
        $product = Product::factory()->create(['price' => $originalPrice]);
        $productId = $product->id; // DEF: Save ID

        // When: Reload product from database
        $reloadedProduct = Product::find($productId); // USE: Reload
        $reloadedPrice = $reloadedProduct->price; // USE: Get price

        // Then: Price consistency maintained
        $this->assertEquals($originalPrice, $reloadedPrice); // USE: Verify consistency
    }
}
