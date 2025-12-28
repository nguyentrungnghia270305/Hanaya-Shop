<?php

namespace Tests\Unit\Database\Seeders;

use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function product_seeder_runs_successfully()
    {
        $seeder = new ProductSeeder();
        
        $this->assertNull($seeder->run());
    }

    /**
     * @test
     */
    public function product_seeder_creates_products()
    {
        $this->seed(ProductSeeder::class);
        
        $this->assertGreaterThan(0, \App\Models\Product\Product::count());
    }

    /**
     * @test
     */
    public function product_seeder_creates_categories()
    {
        $this->seed(ProductSeeder::class);
        
        $this->assertGreaterThan(0, \App\Models\Product\Category::count());
    }

    /**
     * @test
     */
    public function product_seeder_products_have_valid_prices()
    {
        $this->seed(ProductSeeder::class);
        
        $products = \App\Models\Product\Product::all();
        
        foreach ($products as $product) {
            $this->assertGreaterThan(0, $product->price);
        }
    }

    /**
     * @test
     */
    public function product_seeder_products_linked_to_categories()
    {
        $this->seed(ProductSeeder::class);
        
        $products = \App\Models\Product\Product::all();
        
        foreach ($products as $product) {
            $this->assertNotNull($product->category_id);
            $this->assertInstanceOf(\App\Models\Product\Category::class, $product->category);
        }
    }
}
