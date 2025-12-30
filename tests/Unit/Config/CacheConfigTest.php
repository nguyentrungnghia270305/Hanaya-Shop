<?php

namespace Tests\Unit\Config;

use Tests\TestCase;

class CacheConfigTest extends TestCase
{
    /**
     * @test
     */
    public function default_cache_store_is_configured()
    {
        $default = config('cache.default');

        $this->assertNotEmpty($default);
        $this->assertIsString($default);
    }

    /**
     * @test
     */
    public function cache_stores_are_configured()
    {
        $stores = config('cache.stores');

        $this->assertIsArray($stores);
        $this->assertNotEmpty($stores);
    }

    /**
     * @test
     */
    public function file_cache_driver_is_configured()
    {
        $fileStore = config('cache.stores.file');

        $this->assertIsArray($fileStore);
        $this->assertArrayHasKey('driver', $fileStore);
        $this->assertEquals('file', $fileStore['driver']);
    }

    /**
     * @test
     */
    public function cache_prefix_is_configured()
    {
        $prefix = config('cache.prefix');

        $this->assertNotEmpty($prefix);
        $this->assertIsString($prefix);
    }

    /**
     * @test
     */
    public function array_cache_store_exists()
    {
        $arrayStore = config('cache.stores.array');

        $this->assertIsArray($arrayStore);
        $this->assertEquals('array', $arrayStore['driver']);
    }
}
