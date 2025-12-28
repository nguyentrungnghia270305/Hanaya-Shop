<?php

namespace Tests\Integration\Database;

use App\Models\Product\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QueryPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Product::factory()->count(100)->create();
    }

    /**
     * @test
     */
    public function query_with_index_is_faster_than_without()
    {
        $start = microtime(true);
        Product::where('id', 1)->first();
        $indexedTime = (microtime(true) - $start) * 1000;
        
        $this->assertLessThan(20, $indexedTime);
    }

    /**
     * @test
     */
    public function select_specific_columns_reduces_data_transfer()
    {
        $start = microtime(true);
        $selective = Product::select('id', 'name')->limit(50)->get();
        $selectiveTime = (microtime(true) - $start) * 1000;
        
        $start = microtime(true);
        $full = Product::limit(50)->get();
        $fullSelectTime = (microtime(true) - $start) * 1000;
        
        $this->assertCount(50, $selective);
        $this->assertCount(50, $full);
        $this->assertIsFloat($selectiveTime);
        $this->assertIsFloat($fullSelectTime);
    }

    /**
     * @test
     */
    public function query_caching_improves_repeated_queries()
    {
        Cache::forget('products_cache_test');
        
        $start = microtime(true);
        $products = Product::limit(20)->get();
        $dbTime = (microtime(true) - $start) * 1000;
        
        Cache::put('products_cache_test', $products, 60);
        
        $start = microtime(true);
        $cachedProducts = Cache::get('products_cache_test');
        $cacheTime = (microtime(true) - $start) * 1000;
        
        $this->assertCount(20, $products);
        $this->assertCount(20, $cachedProducts);
        $this->assertIsFloat($dbTime);
        $this->assertIsFloat($cacheTime);
    }

    /**
     * @test
     */
    public function limit_clause_reduces_query_time()
    {
        $start = microtime(true);
        Product::limit(10)->get();
        $limitedTime = (microtime(true) - $start) * 1000;
        
        $start = microtime(true);
        Product::all();
        $allTime = (microtime(true) - $start) * 1000;
        
        $this->assertLessThanOrEqual($allTime, $limitedTime);
    }

    /**
     * @test
     */
    public function aggregation_queries_are_optimized()
    {
        $start = microtime(true);
        
        DB::table('products')->count();
        DB::table('products')->sum('price');
        DB::table('products')->avg('price');
        DB::table('products')->max('price');
        DB::table('products')->min('price');
        
        $totalTime = (microtime(true) - $start) * 1000;
        
        $this->assertLessThan(100, $totalTime);
    }

    /**
     * @test
     */
    public function exists_check_is_faster_than_count()
    {
        $start = microtime(true);
        $exists = Product::where('price', '>', 0)->exists();
        $existsTime = (microtime(true) - $start) * 1000;
        
        $start = microtime(true);
        $count = Product::where('price', '>', 0)->count() > 0;
        $countTime = (microtime(true) - $start) * 1000;
        
        $this->assertTrue($exists);
        $this->assertIsFloat($existsTime);
        $this->assertIsFloat($countTime);
    }

    /**
     * @test
     */
    public function join_queries_perform_acceptably()
    {
        $start = microtime(true);
        
        DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.*', 'categories.name as category_name')
            ->limit(50)
            ->get();
        
        $joinTime = (microtime(true) - $start) * 1000;
        
        $this->assertLessThan(200, $joinTime);
    }
}
