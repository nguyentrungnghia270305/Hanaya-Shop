<?php

namespace Tests\Feature\Admin\Product;

use App\Models\Product\Category;
use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductCRUDTest extends TestCase
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
    public function admin_can_view_products_list()
    {
        Product::factory()->count(10)->create();
        
        $response = $this->actingAs($this->admin)
            ->get(route('admin.product'));
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.products.index');
    }

    /**
     * @test
     */
    public function admin_can_create_product()
    {
        Storage::fake('public');
        
        $file = UploadedFile::fake()->create('product.jpg', 100, 'image/jpeg');
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.product.store'), [
                'name' => 'Test Product',
                'descriptions' => 'Test description',
                'price' => 100,
                'stock_quantity' => 50,
                'category_id' => $this->category->id,
                'image_url' => $file
            ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'price' => 100
        ]);
    }

    /**
     * @test
     */
    public function admin_can_view_single_product()
    {
        $product = Product::factory()->create();
        
        $response = $this->actingAs($this->admin)
            ->get(route('admin.product.show', $product));
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.products.show');
    }

    /**
     * @test
     */
    public function admin_can_update_product()
    {
        $product = Product::factory()->create(['name' => 'Old Name']);
        
        $response = $this->actingAs($this->admin)
            ->put(route('admin.product.update', $product), [
                'name' => 'Updated Name',
                'descriptions' => 'Updated description',
                'price' => 150,
                'stock_quantity' => 30,
                'category_id' => $this->category->id
            ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'price' => 150
        ]);
    }

    /**
     * @test
     */
    public function admin_can_delete_product()
    {
        $product = Product::factory()->create();
        
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.product.destroy', $product));
        
        $response->assertRedirect();
        $this->assertDatabaseMissing('products', [
            'id' => $product->id
        ]);
    }

    /**
     * @test
     */
    public function non_admin_cannot_access_product_management()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $response = $this->actingAs($user)
            ->get(route('admin.product'));
        
        $response->assertStatus(403);
    }
}
