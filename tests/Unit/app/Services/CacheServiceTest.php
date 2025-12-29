<?php

namespace Tests\Unit\App\Services;

use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * @test
     */
    public function service_has_cache_duration_constants()
    {
        $this->assertIsInt(CacheService::DASHBOARD_CACHE_DURATION);
        $this->assertIsInt(CacheService::PRODUCTS_CACHE_DURATION);
        $this->assertIsInt(CacheService::PRODUCT_DETAIL_DURATION);
    }

    /**
     * @test
     */
    public function can_clear_product_caches()
    {
        Cache::put('dashboard_stats', 'test_data', 60);
        Cache::put('dashboard_recent_products', 'test_data', 60);
        
        CacheService::clearProductCaches();
        
        $this->assertNull(Cache::get('dashboard_stats'));
        $this->assertNull(Cache::get('dashboard_recent_products'));
    }

    /**
     * @test
     */
    public function can_clear_dashboard_cache()
    {
        $keys = [
            'dashboard_stats',
            'dashboard_recent_products',
            'dashboard_categories',
            'dashboard_recent_orders'
        ];
        
        foreach ($keys as $key) {
            Cache::put($key, 'test_data', 60);
        }
        
        CacheService::clearDashboardCache();
        
        foreach ($keys as $key) {
            $this->assertNull(Cache::get($key));
        }
    }

    /**
     * @test
     */
    public function can_clear_specific_product_cache()
    {
        $productId = 123;
        Cache::put("product_detail_{$productId}", 'test_data', 60);
        Cache::put("related_products_{$productId}", 'test_data', 60);
        Cache::put('dashboard_stats', 'test_data', 60);
        
        CacheService::clearProductCache($productId);
        
        $this->assertNull(Cache::get("product_detail_{$productId}"));
        $this->assertNull(Cache::get("related_products_{$productId}"));
        $this->assertNull(Cache::get('dashboard_stats'));
    }

    /**
     * @test
     */
    public function generates_products_index_cache_key()
    {
        $params = ['category' => 1, 'sort' => 'price'];
        
        $key1 = CacheService::getProductsIndexCacheKey($params);
        $key2 = CacheService::getProductsIndexCacheKey($params);
        
        $this->assertEquals($key1, $key2);
        $this->assertStringStartsWith('products_index_', $key1);
    }

    /**
     * @test
     */
    public function generates_product_detail_cache_key()
    {
        $productId = 123;
        
        $key = CacheService::getProductDetailCacheKey($productId);
        
        $this->assertEquals("product_detail_{$productId}", $key);
    }

    /**
     * @test
     */
    public function generates_related_products_cache_key()
    {
        $productId = 456;
        
        $key = CacheService::getRelatedProductsCacheKey($productId);
        
        $this->assertEquals("related_products_{$productId}", $key);
    }

    /**
     * @test
     */
    public function different_params_generate_different_cache_keys()
    {
        $params1 = ['category' => 1];
        $params2 = ['category' => 2];
        
        $key1 = CacheService::getProductsIndexCacheKey($params1);
        $key2 = CacheService::getProductsIndexCacheKey($params2);
        
        $this->assertNotEquals($key1, $key2);
    }

    /**
     * @test
     */
    public function cache_duration_constants_are_reasonable()
    {
        $this->assertGreaterThan(0, CacheService::DASHBOARD_CACHE_DURATION);
        $this->assertGreaterThan(0, CacheService::PRODUCTS_CACHE_DURATION);
        $this->assertGreaterThan(0, CacheService::PRODUCT_DETAIL_DURATION);
        
        // 30 minutes = 1800 seconds
        $this->assertEquals(1800, CacheService::DASHBOARD_CACHE_DURATION);
        // 15 minutes = 900 seconds
        $this->assertEquals(900, CacheService::PRODUCTS_CACHE_DURATION);
    }
}
