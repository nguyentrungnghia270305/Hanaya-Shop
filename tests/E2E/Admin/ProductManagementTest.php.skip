<?php

namespace Tests\E2E\Admin;

use App\Models\Product\Category;
use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->category = Category::factory()->create();
    }

    /**
     * @test
     */
    public function admin_can_view_and_manage_products()
    {
        // Create existing products
        Product::factory()->count(5)->create();
        
        // View products list
        $response = $this->actingAs($this->admin)
            ->get(route('admin.product'));
        
        $response->assertStatus(200);
        
        // View create form
        $response = $this->actingAs($this->admin)
            ->get(route('admin.product.create'));
        
        $response->assertStatus(200);
        
        // Create new product
        $response = $this->actingAs($this->admin)
            ->post(route('admin.product.store'), [
                'name' => 'Rose Bouquet',
                'descriptions' => 'Beautiful red roses',
                'price' => 500000,
                'stock_quantity' => 100,
                'category_id' => $this->category->id
            ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'name' => 'Rose Bouquet',
            'price' => 500000
        ]);
        
        $product = Product::where('name', 'Rose Bouquet')->first();
        
        // View product details
        $response = $this->actingAs($this->admin)
            ->get(route('admin.product.show', $product->id));
        
        $response->assertStatus(200);
        
        // Update product
        $response = $this->actingAs($this->admin)
            ->put(route('admin.product.update', $product->id), [
                'name' => 'Rose Bouquet Premium',
                'descriptions' => 'Beautiful red roses - Premium',
                'price' => 600000,
                'stock_quantity' => 80,
                'category_id' => $this->category->id
            ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Rose Bouquet Premium',
            'price' => 600000
        ]);
        
        // Delete product
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.product.destroy', $product->id));
        
        $response->assertRedirect();
        $this->assertDatabaseMissing('products', [
            'id' => $product->id
        ]);
    }

    /**
     * @test
     */
    public function admin_can_change_product_category()
    {
        $category1 = Category::factory()->create(['name' => 'Roses']);
        $category2 = Category::factory()->create(['name' => 'Tulips']);
        
        $product = Product::factory()->create(['category_id' => $category1->id]);
        
        // Change category
        $response = $this->actingAs($this->admin)
            ->put(route('admin.product.update', $product->id), [
                'name' => $product->name,
                'descriptions' => $product->descriptions,
                'price' => $product->price,
                'stock_quantity' => $product->stock_quantity,
                'category_id' => $category2->id
            ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'category_id' => $category2->id
        ]);
    }

    /**
     * @test
     */
    public function product_creation_validates_required_fields()
    {
        // Test missing required fields
        $response = $this->actingAs($this->admin)
            ->post(route('admin.product.store'), [
                'descriptions' => 'Test product'
            ]);
        
        $response->assertSessionHasErrors(['name', 'price', 'stock_quantity', 'category_id']);
        
        // Test invalid price
        $response = $this->actingAs($this->admin)
            ->post(route('admin.product.store'), [
                'name' => 'Test Product',
                'price' => -100,
                'stock_quantity' => 10,
                'category_id' => $this->category->id
            ]);
        
        $response->assertSessionHasErrors('price');
    }
}
