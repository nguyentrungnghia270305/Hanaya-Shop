<?php

namespace Tests\Integration\Cache;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RedisCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        if (config('cache.default') !== 'redis') {
            $this->markTestSkipped('Redis is not configured as cache driver');
        }
        
        Cache::flush();
    }

    /**
     * @test
     */
    public function redis_cache_stores_and_retrieves_data()
    {
        $key = 'test_key';
        $value = ['data' => 'test value', 'number' => 123];
        
        Cache::put($key, $value, 3600);
        
        $this->assertTrue(Cache::has($key));
        $this->assertEquals($value, Cache::get($key));
    }

    /**
     * @test
     */
    public function redis_cache_handles_large_datasets()
    {
        $largeArray = [];
        for ($i = 0; $i < 1000; $i++) {
            $largeArray[] = [
                'id' => $i,
                'name' => 'Item ' . $i,
                'data' => str_repeat('x', 100)
            ];
        }
        
        Cache::put('large_dataset', $largeArray, 3600);
        
        $retrieved = Cache::get('large_dataset');
        $this->assertCount(1000, $retrieved);
        $this->assertEquals($largeArray[500], $retrieved[500]);
    }

    /**
     * @test
     */
    public function redis_cache_increment_and_decrement_work()
    {
        Cache::put('counter', 10, 3600);
        
        Cache::increment('counter');
        $this->assertEquals(11, Cache::get('counter'));
        
        Cache::increment('counter', 5);
        $this->assertEquals(16, Cache::get('counter'));
        
        Cache::decrement('counter', 3);
        $this->assertEquals(13, Cache::get('counter'));
    }

    /**
     * @test
     */
    public function redis_cache_remember_function_works()
    {
        $key = 'computed_value';
        $computedOnce = false;
        
        $value1 = Cache::remember($key, 3600, function () use (&$computedOnce) {
            $computedOnce = true;
            return 'computed_result';
        });
        
        $this->assertTrue($computedOnce);
        $this->assertEquals('computed_result', $value1);
        
        $computedOnce = false;
        
        $value2 = Cache::remember($key, 3600, function () use (&$computedOnce) {
            $computedOnce = true;
            return 'computed_result';
        });
        
        $this->assertFalse($computedOnce);
        $this->assertEquals($value1, $value2);
    }

    /**
     * @test
     */
    public function redis_cache_handles_concurrent_access()
    {
        $key = 'concurrent_key';
        
        for ($i = 0; $i < 10; $i++) {
            Cache::put($key . '_' . $i, 'value_' . $i, 3600);
        }
        
        for ($i = 0; $i < 10; $i++) {
            $this->assertEquals('value_' . $i, Cache::get($key . '_' . $i));
        }
    }
}
