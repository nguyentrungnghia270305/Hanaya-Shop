<?php

namespace Tests\Unit\App\Models\Product;

use App\Models\Cart\Cart;
use App\Models\Order\OrderDetail;
use App\Models\Product\Category;
use App\Models\Product\Product;
use App\Models\Product\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function product_can_be_created_with_required_fields()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 100.00,
            'category_id' => $category->id,
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'price' => 100.00,
            'category_id' => $category->id,
        ]);
    }

    /** @test */
    public function product_belongs_to_category()
    {
        $category = Category::factory()->create(['name' => 'Flowers']);
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals('Flowers', $product->category->name);
    }

    /** @test */
    public function product_calculates_discounted_price_correctly()
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'discount_percent' => 20,
        ]);

        $discountedPrice = $product->getDiscountedPrice();

        $this->assertEquals(80.00, $discountedPrice);
    }

    /** @test */
    public function product_can_override_discount_percentage()
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'discount_percent' => 20,
        ]);

        $discountedPrice = $product->getDiscountedPrice(50);

        $this->assertEquals(50.00, $discountedPrice);
    }

    /** @test */
    public function product_discounted_price_accessor_works()
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'discount_percent' => 25,
        ]);

        $this->assertEquals(75.00, $product->discounted_price);
    }

    /** @test */
    public function product_returns_original_price_when_no_discount()
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'discount_percent' => 0,
        ]);

        $this->assertEquals(100.00, $product->discounted_price);
    }

    /** @test */
    public function product_has_reviews_relationship()
    {
        $product = Product::factory()->create();
        $reviews = Review::factory()->count(3)->create(['product_id' => $product->id]);

        $this->assertCount(3, $product->reviews);
        $this->assertInstanceOf(Review::class, $product->reviews->first());
    }

    /** @test */
    public function product_calculates_average_rating_from_reviews()
    {
        $product = Product::factory()->create();
        Review::factory()->create(['product_id' => $product->id, 'rating' => 4]);
        Review::factory()->create(['product_id' => $product->id, 'rating' => 5]);
        Review::factory()->create(['product_id' => $product->id, 'rating' => 3]);

        $this->assertEquals(4.0, $product->average_rating);
    }

    /** @test */
    public function product_returns_default_rating_when_no_reviews()
    {
        $product = Product::factory()->create();

        $this->assertEquals(5, $product->average_rating);
    }

    /** @test */
    public function product_counts_reviews_correctly()
    {
        $product = Product::factory()->create();
        Review::factory()->count(7)->create(['product_id' => $product->id]);

        $this->assertEquals(7, $product->review_count);
    }
}
