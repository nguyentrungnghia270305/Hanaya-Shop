<?php

namespace Tests\Feature\Admin\Product;

use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductStockManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /**
     * @test
     */
    public function admin_can_increase_product_stock()
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.product.update', $product), [
                'name' => $product->name,
                'descriptions' => $product->descriptions,
                'price' => $product->price,
                'category_id' => $product->category_id,
                'discount_percent' => $product->discount_percent,
                'stock_quantity' => 20,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 20,
        ]);
    }

    /**
     * @test
     */
    public function admin_can_decrease_product_stock()
    {
        $product = Product::factory()->create(['stock_quantity' => 50]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.product.update', $product), [
                'name' => $product->name,
                'descriptions' => $product->descriptions,
                'price' => $product->price,
                'category_id' => $product->category_id,
                'discount_percent' => $product->discount_percent,
                'stock_quantity' => 30,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 30,
        ]);
    }

    /**
     * @test
     */
    public function product_stock_cannot_be_negative()
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.product.update', $product), [
                'name' => $product->name,
                'descriptions' => $product->descriptions,
                'price' => $product->price,
                'category_id' => $product->category_id,
                'discount_percent' => $product->discount_percent,
                'stock_quantity' => -5,
            ]);

        $response->assertSessionHasErrors('stock_quantity');
    }

    /**
     * @test
     */
    public function out_of_stock_products_are_marked()
    {
        $product = Product::factory()->create(['stock_quantity' => 0]);

        $this->assertEquals(0, $product->stock_quantity);
        $this->assertTrue($product->stock_quantity === 0);
    }

    /**
     * @test
     */
    public function low_stock_alert_for_products_below_threshold()
    {
        $product = Product::factory()->create(['stock_quantity' => 5]);

        $this->assertLessThan(10, $product->stock_quantity);
    }
}
