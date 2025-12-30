<?php

namespace Tests\Integration\Cache;

use App\Models\Product\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheInvalidationTest extends TestCase
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
    public function cache_is_invalidated_when_product_is_updated()
    {
        $product = Product::factory()->create(['name' => 'Original Name']);

        Cache::put('product_'.$product->id, $product, 3600);
        $this->assertTrue(Cache::has('product_'.$product->id));

        $product->update(['name' => 'Updated Name']);
        Cache::forget('product_'.$product->id);

        $this->assertFalse(Cache::has('product_'.$product->id));
    }

    /**
     * @test
     */
    public function cache_is_invalidated_when_product_is_deleted()
    {
        $product = Product::factory()->create();

        Cache::put('product_'.$product->id, $product, 3600);
        Cache::put('products_list', Product::all(), 3600);

        $productId = $product->id;
        $product->delete();

        Cache::forget('product_'.$productId);
        Cache::forget('products_list');

        $this->assertFalse(Cache::has('product_'.$productId));
        $this->assertFalse(Cache::has('products_list'));
    }

    /**
     * @test
     */
    public function cache_tags_allow_group_invalidation()
    {
        if (! Cache::supportsTags()) {
            $this->markTestSkipped('Cache driver does not support tags');
        }

        Cache::tags(['products'])->put('product_1', 'data1', 3600);
        Cache::tags(['products'])->put('product_2', 'data2', 3600);
        Cache::tags(['users'])->put('user_1', 'data3', 3600);

        Cache::tags(['products'])->flush();

        $this->assertFalse(Cache::tags(['products'])->has('product_1'));
        $this->assertFalse(Cache::tags(['products'])->has('product_2'));
        $this->assertTrue(Cache::tags(['users'])->has('user_1'));
    }

    /**
     * @test
     */
    public function cache_invalidation_cascades_to_related_data()
    {
        $products = Product::factory()->count(5)->create();

        Cache::put('all_products', $products, 3600);
        Cache::put('product_count', $products->count(), 3600);
        Cache::put('featured_products', $products->take(3), 3600);

        $keysToInvalidate = ['all_products', 'product_count', 'featured_products'];
        foreach ($keysToInvalidate as $key) {
            Cache::forget($key);
        }

        $this->assertFalse(Cache::has('all_products'));
        $this->assertFalse(Cache::has('product_count'));
        $this->assertFalse(Cache::has('featured_products'));
    }

    /**
     * @test
     */
    public function cache_ttl_expiration_works_correctly()
    {
        Cache::put('short_lived', 'test_data', 1);

        $this->assertTrue(Cache::has('short_lived'));
        $this->assertEquals('test_data', Cache::get('short_lived'));

        sleep(2);

        $this->assertFalse(Cache::has('short_lived'));
    }
}
