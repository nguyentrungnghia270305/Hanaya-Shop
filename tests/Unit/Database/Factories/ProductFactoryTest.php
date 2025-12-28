<?php

namespace Tests\Unit\Database\Factories;

use App\Models\Product\Category;
use App\Models\Product\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductFactoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function factory_creates_product_with_required_fields()
    {
        $product = Product::factory()->create();
        
        $this->assertNotNull($product->name);
        $this->assertNotNull($product->price);
        $this->assertNotNull($product->category_id);
        $this->assertDatabaseHas('products', [
            'id' => $product->id
        ]);
    }

    /**
     * @test
     */
    public function factory_generates_descriptions()
    {
        $product = Product::factory()->create();
        
        $this->assertNotNull($product->descriptions);
        $this->assertIsString($product->descriptions);
    }

    /**
     * @test
     */
    public function factory_sets_default_stock_quantity()
    {
        $product = Product::factory()->create();
        
        $this->assertIsInt($product->stock_quantity);
        $this->assertGreaterThanOrEqual(0, $product->stock_quantity);
    }

    /**
     * @test
     */
    public function factory_sets_discount_percent()
    {
        $product = Product::factory()->create();

        $this->assertIsNumeric($product->discount_percent);
        $this->assertGreaterThanOrEqual(0, $product->discount_percent);
        $this->assertLessThanOrEqual(100, $product->discount_percent);
    }    /**
     * @test
     */
    public function factory_can_override_attributes()
    {
        $category = Category::factory()->create();
        
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 999.99,
            'category_id' => $category->id,
            'stock_quantity' => 50
        ]);
        
        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals(999.99, $product->price);
        $this->assertEquals($category->id, $product->category_id);
        $this->assertEquals(50, $product->stock_quantity);
    }

    /**
     * @test
     */
    public function factory_creates_category_automatically()
    {
        $product = Product::factory()->create();
        
        $this->assertInstanceOf(Category::class, $product->category);
    }

    /**
     * @test
     */
    public function factory_can_create_multiple_products()
    {
        $products = Product::factory()->count(5)->create();
        
        $this->assertCount(5, $products);
        $this->assertEquals(5, Product::count());
    }
}
