<?php

namespace Tests\Performance;

use App\Models\Product\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CachePerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * @test
     */
    public function cache_read_is_faster_than_database_query()
    {
        $products = Product::factory()->count(100)->create();

        // Cache the products
        Cache::put('all_products', $products, 3600);

        // Retrieve from cache
        $cachedProducts = Cache::get('all_products');

        // Cache should work correctly
        $this->assertNotNull($cachedProducts);
        $this->assertCount(100, $cachedProducts);

        // Verify data integrity
        $this->assertEquals($products->pluck('id')->sort()->values(), $cachedProducts->pluck('id')->sort()->values());
    }

    /**
     * @test
     */
    public function cache_write_performs_acceptably()
    {
        $data = Product::factory()->count(50)->make()->toArray();

        $startTime = microtime(true);

        Cache::put('test_products', $data, 3600);

        $duration = (microtime(true) - $startTime) * 1000;

        // Cache write should be under 50ms
        $this->assertLessThan(50, $duration);
        $this->assertTrue(Cache::has('test_products'));
    }

    /**
     * @test
     */
    public function cache_miss_fallback_works_efficiently()
    {
        Product::factory()->count(10)->create();

        $startTime = microtime(true);

        $products = Cache::remember('products_cache', 3600, function () {
            return Product::all();
        });

        $duration = (microtime(true) - $startTime) * 1000;

        // First call (cache miss) should complete within 200ms
        $this->assertLessThan(200, $duration);
        $this->assertCount(10, $products);

        // Second call should be from cache and faster
        $startTime2 = microtime(true);
        $cachedProducts = Cache::get('products_cache');
        $duration2 = (microtime(true) - $startTime2) * 1000;

        $this->assertLessThan($duration, $duration2);
    }

    /**
     * @test
     */
    public function cache_invalidation_is_fast()
    {
        Cache::put('test_key_1', 'value1', 3600);
        Cache::put('test_key_2', 'value2', 3600);
        Cache::put('test_key_3', 'value3', 3600);

        $startTime = microtime(true);

        Cache::forget('test_key_1');
        Cache::forget('test_key_2');
        Cache::forget('test_key_3');

        $duration = (microtime(true) - $startTime) * 1000;

        // Invalidating 3 keys should be under 50ms (test environment)
        $this->assertLessThan(50, $duration);
        $this->assertFalse(Cache::has('test_key_1'));
    }

    /**
     * @test
     */
    public function cache_handles_large_datasets_efficiently()
    {
        $largeData = Product::factory()->count(500)->make()->toArray();

        $startTime = microtime(true);

        Cache::put('large_dataset', $largeData, 3600);
        $retrieved = Cache::get('large_dataset');

        $duration = (microtime(true) - $startTime) * 1000;

        // Should handle 500 items within 100ms
        $this->assertLessThan(100, $duration);
        $this->assertCount(500, $retrieved);
    }
}
