<?php

namespace Tests\Performance;

use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemoryUsageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function loading_large_collection_uses_acceptable_memory()
    {
        Product::factory()->count(500)->create();

        $memoryBefore = memory_get_usage();

        $products = Product::all();

        $memoryAfter = memory_get_usage();
        $memoryUsed = ($memoryAfter - $memoryBefore) / 1024 / 1024; // Convert to MB

        // Should use less than 10MB for 500 products
        $this->assertLessThan(10, $memoryUsed, "Memory used: {$memoryUsed}MB");
        $this->assertCount(500, $products);
    }

    /**
     * @test
     */
    public function chunk_processing_reduces_memory_footprint()
    {
        Product::factory()->count(1000)->create();

        // Process all at once
        $memoryBefore1 = memory_get_usage();
        $allProducts = Product::all();
        $count1 = $allProducts->count();
        $memoryAfter1 = memory_get_usage();
        $memoryAll = ($memoryAfter1 - $memoryBefore1) / 1024 / 1024;

        unset($allProducts);
        gc_collect_cycles();

        // Process in chunks
        $memoryBefore2 = memory_get_usage();
        $count2 = 0;
        Product::chunk(100, function ($products) use (&$count2) {
            $count2 += $products->count();
        });
        $memoryAfter2 = memory_get_usage();
        $memoryChunk = ($memoryAfter2 - $memoryBefore2) / 1024 / 1024;

        // Chunking should use less memory
        $this->assertLessThan($memoryAll, $memoryChunk);
        $this->assertEquals(1000, $count1);
        $this->assertEquals(1000, $count2);
    }

    /**
     * @test
     */
    public function cursor_iteration_is_memory_efficient()
    {
        Product::factory()->count(500)->create();

        $memoryBefore = memory_get_usage();
        $peakMemory = $memoryBefore;

        $count = 0;
        foreach (Product::cursor() as $product) {
            $count++;
            $currentMemory = memory_get_usage();
            if ($currentMemory > $peakMemory) {
                $peakMemory = $currentMemory;
            }
        }

        $memoryIncrease = ($peakMemory - $memoryBefore) / 1024 / 1024;

        // Cursor should use minimal additional memory (under 5MB)
        $this->assertLessThan(5, $memoryIncrease);
        $this->assertEquals(500, $count);
    }

    /**
     * @test
     */
    public function peak_memory_stays_within_limits()
    {
        User::factory()->count(100)->create();
        Product::factory()->count(200)->create();

        $memoryBefore = memory_get_peak_usage();

        // Perform multiple operations
        $users = User::with('addresses')->get();
        $products = Product::with('category')->get();
        $featured = Product::where('stock_quantity', '>', 10)->get();

        $memoryAfter = memory_get_peak_usage();
        $peakIncrease = ($memoryAfter - $memoryBefore) / 1024 / 1024;

        // Peak memory increase should be under 20MB
        $this->assertLessThan(20, $peakIncrease, "Peak memory increase: {$peakIncrease}MB");
    }
}
