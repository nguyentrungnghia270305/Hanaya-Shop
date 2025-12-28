<?php

namespace Tests\Performance;

use App\Models\Product\Product;
use App\Models\User;
use App\Models\Order\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseQueryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function simple_select_query_performs_efficiently()
    {
        Product::factory()->count(100)->create();
        
        DB::enableQueryLog();
        $startTime = microtime(true);
        
        $products = Product::all();
        
        $duration = (microtime(true) - $startTime) * 1000;
        $queries = DB::getQueryLog();
        
        // Should execute in under 100ms
        $this->assertLessThan(100, $duration);
        // Should use only 1 query
        $this->assertCount(1, $queries);
        $this->assertCount(100, $products);
    }

    /**
     * @test
     */
    public function eager_loading_reduces_query_count()
    {
        $products = Product::factory()->count(10)->create();
        
        // Without eager loading (N+1 problem)
        DB::enableQueryLog();
        $products1 = Product::all();
        foreach ($products1 as $product) {
            $category = $product->category;
        }
        $queriesWithout = count(DB::getQueryLog());
        
        // With eager loading
        DB::flushQueryLog();
        $products2 = Product::with('category')->get();
        foreach ($products2 as $product) {
            $category = $product->category;
        }
        $queriesWith = count(DB::getQueryLog());
        
        // Eager loading should use fewer queries
        $this->assertLessThan($queriesWithout, $queriesWith);
        $this->assertLessThanOrEqual(2, $queriesWith); // 1 for products, 1 for categories
    }

    /**
     * @test
     */
    public function indexed_column_search_is_fast()
    {
        Product::factory()->count(500)->create();
        
        $startTime = microtime(true);
        
        // Search by ID (indexed column)
        $product = Product::find(250);
        
        $duration = (microtime(true) - $startTime) * 1000;
        
        // Indexed search should be very fast
        $this->assertLessThan(10, $duration);
    }

    /**
     * @test
     */
    public function pagination_query_performs_efficiently()
    {
        Product::factory()->count(200)->create();
        
        DB::enableQueryLog();
        $startTime = microtime(true);
        
        $products = Product::paginate(20);
        
        $duration = (microtime(true) - $startTime) * 1000;
        $queries = DB::getQueryLog();
        
        // Pagination should complete within 150ms
        $this->assertLessThan(150, $duration);
        // Should execute 2 queries (count + data)
        $this->assertCount(2, $queries);
        $this->assertEquals(20, $products->count());
    }

    /**
     * @test
     */
    public function bulk_insert_is_efficient()
    {
        $productsData = Product::factory()->count(100)->make()->map(function ($product) {
            $data = $product->toArray();
            // Convert timestamps to MySQL format
            if (isset($data['created_at'])) {
                $data['created_at'] = now()->format('Y-m-d H:i:s');
            }
            if (isset($data['updated_at'])) {
                $data['updated_at'] = now()->format('Y-m-d H:i:s');
            }
            return $data;
        })->toArray();
        
        $startTime = microtime(true);
        
        Product::insert($productsData);
        
        $duration = (microtime(true) - $startTime) * 1000;
        
        // Bulk insert should be under 300ms
        $this->assertLessThan(300, $duration);
        $this->assertDatabaseCount('products', 100);
    }

    /**
     * @test
     */
    public function query_with_where_clause_performs_well()
    {
        Product::factory()->count(300)->create();
        Product::factory()->count(10)->create(['stock_quantity' => 0]);
        
        DB::enableQueryLog();
        $startTime = microtime(true);
        
        $inStock = Product::where('stock_quantity', '>', 0)->get();
        
        $duration = (microtime(true) - $startTime) * 1000;
        $queries = DB::getQueryLog();
        
        // WHERE query should complete within 100ms
        $this->assertLessThan(100, $duration);
        $this->assertCount(1, $queries);
        $this->assertGreaterThan(290, $inStock->count());
    }
}
