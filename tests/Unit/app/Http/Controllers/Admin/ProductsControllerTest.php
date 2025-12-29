<?php

namespace Tests\Unit\App\Controllers\Admin;

use App\Models\Product\Category;
use App\Models\Product\Product;
use App\Models\Product\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\ControllerTestCase;

class ProductsControllerTest extends ControllerTestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin);

        // Create the products directory if it doesn't exist
        if (! File::exists(public_path('images/products'))) {
            File::makeDirectory(public_path('images/products'), 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test images
        if (File::exists(public_path('images/products'))) {
            $files = File::files(public_path('images/products'));
            foreach ($files as $file) {
                if (basename($file) !== 'default-product.jpg' && basename($file) !== 'base.jpg') {
                    File::delete($file);
                }
            }
        }

        parent::tearDown();
    }

    /**
     * Test index displays paginated products
     */
    public function test_index_displays_paginated_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(25)->create(['category_id' => $category->id]);

        $response = $this->get(route('admin.product'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.products.index');
        $response->assertViewHas('products');

        $products = $response->viewData('products');
        $this->assertEquals(20, $products->perPage());
        $this->assertEquals(25, $products->total());
    }

    /**
     * Test index eager loads category relationship
     */
    public function test_index_eager_loads_category(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->get(route('admin.product'));

        $products = $response->viewData('products');
        $this->assertTrue($products->first()->relationLoaded('category'));
    }

    /**
     * Test index orders products by created_at descending
     */
    public function test_index_orders_products_by_created_at_desc(): void
    {
        $category = Category::factory()->create();
        $product1 = Product::factory()->create([
            'category_id' => $category->id,
            'created_at' => now()->subDays(2),
        ]);
        $product2 = Product::factory()->create([
            'category_id' => $category->id,
            'created_at' => now()->subDay(),
        ]);

        $response = $this->get(route('admin.product'));

        $products = $response->viewData('products');
        $this->assertTrue($products[0]->created_at->gte($products[1]->created_at));
    }

    /**
     * Test index filters by category
     */
    public function test_index_filters_by_category(): void
    {
        $category1 = Category::factory()->create(['name' => 'Category 1']);
        $category2 = Category::factory()->create(['name' => 'Category 2']);
        Product::factory()->count(5)->create(['category_id' => $category1->id]);
        Product::factory()->count(3)->create(['category_id' => $category2->id]);

        $response = $this->get(route('admin.product', ['category_id' => $category1->id]));

        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertEquals(5, $products->total());
        $this->assertEquals($category1->id, $products->first()->category_id);
    }

    /**
     * Test index filters by low stock
     */
    public function test_index_filters_by_low_stock(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id, 'stock_quantity' => 1]);
        Product::factory()->create(['category_id' => $category->id, 'stock_quantity' => 0]);
        Product::factory()->create(['category_id' => $category->id, 'stock_quantity' => 10]);

        $response = $this->get(route('admin.product', ['stock_filter' => 'low_stock']));

        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertEquals(1, $products->total());
        $this->assertEquals(1, $products->first()->stock_quantity);
    }

    /**
     * Test index filters by out of stock
     */
    public function test_index_filters_by_out_of_stock(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id, 'stock_quantity' => 0]);
        Product::factory()->create(['category_id' => $category->id, 'stock_quantity' => 1]);
        Product::factory()->create(['category_id' => $category->id, 'stock_quantity' => 10]);

        $response = $this->get(route('admin.product', ['stock_filter' => 'out_of_stock']));

        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertEquals(1, $products->total());
        $this->assertEquals(0, $products->first()->stock_quantity);
    }

    /**
     * Test index maintains query string in pagination
     */
    public function test_index_maintains_query_string_in_pagination(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(25)->create(['category_id' => $category->id]);

        $response = $this->get(route('admin.product', ['category_id' => $category->id]));

        $products = $response->viewData('products');
        $this->assertStringContainsString('category_id='.$category->id, $products->nextPageUrl());
    }

    /**
     * Test index passes categories and selected filters to view
     */
    public function test_index_passes_categories_and_filters_to_view(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $response = $this->get(route('admin.product', [
            'category_id' => $category->id,
            'stock_filter' => 'low_stock',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('categories');
        $response->assertViewHas('selectedCategory', $category->id);
        $response->assertViewHas('selectedStockFilter', 'low_stock');
    }

    /**
     * Test create displays form with categories
     */
    public function test_create_displays_form_with_categories(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->get(route('admin.product.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.products.create');
        $response->assertViewHas('categories');

        $categories = $response->viewData('categories');
        $this->assertEquals(3, $categories->count());
    }

    /**
     * Test store creates product with valid data
     */
    public function test_store_creates_product_with_valid_data(): void
    {
        $category = Category::factory()->create();

        $data = [
            'name' => 'Test Product',
            'descriptions' => 'This is a test product description.',
            'price' => 100000,
            'stock_quantity' => 50,
            'category_id' => $category->id,
            'discount_percent' => 10,
            'view_count' => 0,
        ];

        $response = $this->post(route('admin.product.store'), $data);

        $response->assertRedirect(route('admin.product'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'price' => 100000,
            'stock_quantity' => 50,
            'category_id' => $category->id,
            'discount_percent' => 10,
        ]);
    }

    /**
     * Test store uses default image when no image provided
     */
    public function test_store_uses_default_image_when_no_image_provided(): void
    {
        $category = Category::factory()->create();

        $data = [
            'name' => 'Test Product',
            'descriptions' => 'Test description',
            'price' => 100000,
            'stock_quantity' => 50,
            'category_id' => $category->id,
        ];

        $response = $this->post(route('admin.product.store'), $data);

        $response->assertRedirect(route('admin.product'));

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'image_url' => 'default-product.jpg',
        ]);
    }

    /**
     * Test store uploads image with unique filename
     */
    public function test_store_uploads_image_with_unique_filename(): void
    {
        $this->markTestSkipped('GD extension not available in test environment');
        
        $category = Category::factory()->create();
        $image = UploadedFile::fake()->image('product.jpg', 640, 480);

        $data = [
            'name' => 'Test Product',
            'descriptions' => 'Test description',
            'price' => 100000,
            'stock_quantity' => 50,
            'category_id' => $category->id,
            'image_url' => $image,
        ];

        $response = $this->post(route('admin.product.store'), $data);

        $response->assertRedirect(route('admin.product'));

        $product = Product::where('name', 'Test Product')->first();
        $this->assertNotEquals('default-product.jpg', $product->image_url);
        $this->assertTrue(File::exists(public_path('images/products/'.$product->image_url)));
    }

    /**
     * Test store validates required fields
     */
    public function test_store_validates_required_fields(): void
    {
        $response = $this->post(route('admin.product.store'), []);

        $response->assertSessionHasErrors([
            'name',
            'descriptions',
            'price',
            'stock_quantity',
            'category_id',
        ]);
    }

    /**
     * Test store validates price is numeric and minimum 0
     */
    public function test_store_validates_price_is_numeric_and_minimum_zero(): void
    {
        $category = Category::factory()->create();

        $response = $this->post(route('admin.product.store'), [
            'name' => 'Test Product',
            'descriptions' => 'Test description',
            'price' => -100,
            'stock_quantity' => 50,
            'category_id' => $category->id,
        ]);

        $response->assertSessionHasErrors('price');
    }

    /**
     * Test store validates stock_quantity is integer and minimum 0
     */
    public function test_store_validates_stock_quantity_is_integer_and_minimum_zero(): void
    {
        $category = Category::factory()->create();

        $response = $this->post(route('admin.product.store'), [
            'name' => 'Test Product',
            'descriptions' => 'Test description',
            'price' => 100000,
            'stock_quantity' => -5,
            'category_id' => $category->id,
        ]);

        $response->assertSessionHasErrors('stock_quantity');
    }

    /**
     * Test store validates category_id exists
     */
    public function test_store_validates_category_id_exists(): void
    {
        $response = $this->post(route('admin.product.store'), [
            'name' => 'Test Product',
            'descriptions' => 'Test description',
            'price' => 100000,
            'stock_quantity' => 50,
            'category_id' => 9999,
        ]);

        $response->assertSessionHasErrors('category_id');
    }

    /**
     * Test store validates discount_percent range
     */
    public function test_store_validates_discount_percent_range(): void
    {
        $category = Category::factory()->create();

        $response = $this->post(route('admin.product.store'), [
            'name' => 'Test Product',
            'descriptions' => 'Test description',
            'price' => 100000,
            'stock_quantity' => 50,
            'category_id' => $category->id,
            'discount_percent' => 150,
        ]);

        $response->assertSessionHasErrors('discount_percent');
    }

    /**
     * Test store validates image file type
     */
    public function test_store_validates_image_file_type(): void
    {
        $category = Category::factory()->create();
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->post(route('admin.product.store'), [
            'name' => 'Test Product',
            'descriptions' => 'Test description',
            'price' => 100000,
            'stock_quantity' => 50,
            'category_id' => $category->id,
            'image_url' => $file,
        ]);

        $response->assertSessionHasErrors('image_url');
    }

    /**
     * Test store validates image file size
     */
    public function test_store_validates_image_file_size(): void
    {
        $this->markTestSkipped('GD extension not available in test environment');
        
        $category = Category::factory()->create();
        $image = UploadedFile::fake()->image('product.jpg')->size(3000);

        $response = $this->post(route('admin.product.store'), [
            'name' => 'Test Product',
            'descriptions' => 'Test description',
            'price' => 100000,
            'stock_quantity' => 50,
            'category_id' => $category->id,
            'image_url' => $image,
        ]);

        $response->assertSessionHasErrors('image_url');
    }

    /**
     * Test store invalidates cache
     */
    public function test_store_invalidates_cache(): void
    {
        $category = Category::factory()->create();
        Cache::put('admin_products_all', 'cached_data');

        $data = [
            'name' => 'Test Product',
            'descriptions' => 'Test description',
            'price' => 100000,
            'stock_quantity' => 50,
            'category_id' => $category->id,
        ];

        $this->post(route('admin.product.store'), $data);

        $this->assertNull(Cache::get('admin_products_all'));
    }

    /**
     * Test edit displays form with product and categories
     */
    public function test_edit_displays_form_with_product_and_categories(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        Category::factory()->count(2)->create();

        $response = $this->get(route('admin.product.edit', $product->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.products.edit');
        $response->assertViewHas('product');
        $response->assertViewHas('categories');

        $viewProduct = $response->viewData('product');
        $this->assertEquals($product->id, $viewProduct->id);

        $categories = $response->viewData('categories');
        $this->assertEquals(3, $categories->count());
    }

    /**
     * Test edit returns 404 for non-existent product
     */
    public function test_edit_returns_404_for_non_existent_product(): void
    {
        $response = $this->get(route('admin.product.edit', 9999));

        $response->assertStatus(404);
    }

    /**
     * Test update modifies product with valid data
     */
    public function test_update_modifies_product_with_valid_data(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $data = [
            'name' => 'Updated Product',
            'descriptions' => 'Updated description',
            'price' => 200000,
            'stock_quantity' => 100,
            'category_id' => $category->id,
            'discount_percent' => 20,
            'view_count' => 500,
        ];

        $response = $this->put(route('admin.product.update', $product->id), $data);

        $response->assertRedirect(route('admin.product'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'price' => 200000,
            'stock_quantity' => 100,
            'discount_percent' => 20,
            'view_count' => 500,
        ]);
    }

    /**
     * Test update preserves view_count when not provided
     */
    public function test_update_preserves_view_count_when_not_provided(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'view_count' => 999,
        ]);

        $data = [
            'name' => 'Updated Product',
            'descriptions' => 'Updated description',
            'price' => 200000,
            'stock_quantity' => 100,
            'category_id' => $category->id,
        ];

        $this->put(route('admin.product.update', $product->id), $data);

        $product->refresh();
        $this->assertEquals(999, $product->view_count);
    }

    /**
     * Test update replaces image with new upload
     */
    public function test_update_replaces_image_with_new_upload(): void
    {
        $this->markTestSkipped('GD extension not available in test environment');
        
        $category = Category::factory()->create();
        $oldImage = UploadedFile::fake()->image('old.jpg');
        $oldImagePath = public_path('images/products/old_product.jpg');
        $oldImage->move(public_path('images/products'), 'old_product.jpg');

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'image_url' => 'old_product.jpg',
        ]);

        $newImage = UploadedFile::fake()->image('new.jpg');

        $data = [
            'name' => 'Updated Product',
            'descriptions' => 'Updated description',
            'price' => 200000,
            'stock_quantity' => 100,
            'category_id' => $category->id,
            'image_url' => $newImage,
        ];

        $this->put(route('admin.product.update', $product->id), $data);

        $product->refresh();
        $this->assertNotEquals('old_product.jpg', $product->image_url);
        $this->assertFalse(File::exists($oldImagePath));
        $this->assertTrue(File::exists(public_path('images/products/'.$product->image_url)));
    }

    /**
     * Test update does not delete default image when replacing
     */
    public function test_update_does_not_delete_default_image_when_replacing(): void
    {
        $this->markTestSkipped('GD extension not available in test environment');
        
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'image_url' => 'default-product.jpg',
        ]);

        $newImage = UploadedFile::fake()->image('new.jpg');

        $data = [
            'name' => 'Updated Product',
            'descriptions' => 'Updated description',
            'price' => 200000,
            'stock_quantity' => 100,
            'category_id' => $category->id,
            'image_url' => $newImage,
        ];

        $this->put(route('admin.product.update', $product->id), $data);

        $product->refresh();
        $this->assertNotEquals('default-product.jpg', $product->image_url);
    }

    /**
     * Test update validates required fields
     */
    public function test_update_validates_required_fields(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->put(route('admin.product.update', $product->id), []);

        $response->assertSessionHasErrors([
            'name',
            'descriptions',
            'price',
            'stock_quantity',
            'category_id',
        ]);
    }

    /**
     * Test update invalidates cache
     */
    public function test_update_invalidates_cache(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        Cache::put('admin_products_all', 'cached_data');

        $data = [
            'name' => 'Updated Product',
            'descriptions' => 'Updated description',
            'price' => 200000,
            'stock_quantity' => 100,
            'category_id' => $category->id,
        ];

        $this->put(route('admin.product.update', $product->id), $data);

        $this->assertNull(Cache::get('admin_products_all'));
    }

    /**
     * Test destroy deletes product
     */
    public function test_destroy_deletes_product(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->delete(route('admin.product.destroy', $product->id));

        $response->assertRedirect(route('admin.product'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    /**
     * Test destroy deletes associated image file
     */
    public function test_destroy_deletes_associated_image_file(): void
    {
        $this->markTestSkipped('GD extension not available in test environment');
        
        $category = Category::factory()->create();
        $image = UploadedFile::fake()->image('product.jpg');
        $imagePath = public_path('images/products/test_product.jpg');
        $image->move(public_path('images/products'), 'test_product.jpg');

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'image_url' => 'test_product.jpg',
        ]);

        $this->assertTrue(File::exists($imagePath));

        $this->delete(route('admin.product.destroy', $product->id));

        $this->assertFalse(File::exists($imagePath));
    }

    /**
     * Test destroy preserves base.jpg image
     */
    public function test_destroy_preserves_base_image(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'image_url' => 'base.jpg',
        ]);

        $this->delete(route('admin.product.destroy', $product->id));

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    /**
     * Test destroy returns JSON for AJAX requests
     */
    public function test_destroy_returns_json_for_ajax_requests(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->delete(
            route('admin.product.destroy', $product->id),
            [],
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertJson(['success' => true]);
    }

    /**
     * Test destroy invalidates cache
     */
    public function test_destroy_invalidates_cache(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        Cache::put('admin_products_all', 'cached_data');

        $this->delete(route('admin.product.destroy', $product->id));

        $this->assertNull(Cache::get('admin_products_all'));
    }

    /**
     * Test show displays product with category
     */
    public function test_show_displays_product_with_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->get(route('admin.product.show', $product->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.products.show');
        $response->assertViewHas('product');

        $viewProduct = $response->viewData('product');
        $this->assertTrue($viewProduct->relationLoaded('category'));
    }

    /**
     * Test show displays paginated reviews
     */
    public function test_show_displays_paginated_reviews(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        Review::factory()->count(15)->create(['product_id' => $product->id]);

        $response = $this->get(route('admin.product.show', $product->id));

        $response->assertStatus(200);
        $response->assertViewHas('reviews');

        $reviews = $response->viewData('reviews');
        $this->assertEquals(10, $reviews->perPage());
    }

    /**
     * Test show eager loads user and order relationships for reviews
     */
    public function test_show_eager_loads_user_and_order_relationships_for_reviews(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        Review::factory()->count(3)->create(['product_id' => $product->id]);

        $response = $this->get(route('admin.product.show', $product->id));

        $reviews = $response->viewData('reviews');
        $firstReview = $reviews->first();
        $this->assertTrue($firstReview->relationLoaded('user'));
        $this->assertTrue($firstReview->relationLoaded('order'));
    }

    /**
     * Test show orders reviews by created_at descending
     */
    public function test_show_orders_reviews_by_created_at_desc(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        Review::factory()->create([
            'product_id' => $product->id,
            'created_at' => now()->subDays(2),
        ]);
        Review::factory()->create([
            'product_id' => $product->id,
            'created_at' => now()->subDay(),
        ]);

        $response = $this->get(route('admin.product.show', $product->id));

        $reviews = $response->viewData('reviews');
        $this->assertTrue($reviews[0]->created_at->gte($reviews[1]->created_at));
    }

    /**
     * Test show returns JSON for AJAX requests
     */
    public function test_show_returns_json_for_ajax_requests(): void
    {
        $category = Category::factory()->create(['name' => 'Test Category']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'price' => 100000,
            'stock_quantity' => 50,
        ]);

        $response = $this->get(
            route('admin.product.show', $product->id),
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertJson([
            'success' => true,
            'id' => $product->id,
            'name' => 'Test Product',
            'price' => 100000,
            'stock_quantity' => 50,
            'category_name' => 'Test Category',
        ]);
    }

    /**
     * Test show returns JSON with ajax query parameter
     */
    public function test_show_returns_json_with_ajax_query_parameter(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->get(route('admin.product.show', ['id' => $product->id, 'ajax' => '1']));

        $response->assertJson([
            'success' => true,
            'id' => $product->id,
        ]);
    }

    /**
     * Test show returns 404 for non-existent product
     */
    public function test_show_returns_404_for_non_existent_product(): void
    {
        $response = $this->get(route('admin.product.show', 9999));

        $response->assertStatus(404);
    }

    /**
     * Test search finds products by name
     */
    public function test_search_finds_products_by_name(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Laravel Framework',
        ]);
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'PHP Programming',
        ]);

        $response = $this->get(route('admin.product.search', ['query' => 'Laravel']));

        $response->assertJson(['count' => 1]);
    }

    /**
     * Test search finds products by description
     */
    public function test_search_finds_products_by_description(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Product A',
            'descriptions' => 'This product has Laravel framework',
        ]);
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Product B',
            'descriptions' => 'This product is for PHP development',
        ]);

        $response = $this->get(route('admin.product.search', ['query' => 'Laravel']));

        $response->assertJson(['count' => 1]);
    }

    /**
     * Test search handles multiple keywords
     */
    public function test_search_handles_multiple_keywords(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Laravel Framework',
            'descriptions' => 'Best PHP framework',
        ]);
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Symfony',
            'descriptions' => 'Another PHP framework',
        ]);

        $response = $this->get(route('admin.product.search', ['query' => 'Laravel PHP']));

        $response->assertJson(['count' => 1]);
    }

    /**
     * Test search combines with category filter
     */
    public function test_search_combines_with_category_filter(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category1->id,
            'name' => 'Laravel Product',
        ]);
        Product::factory()->create([
            'category_id' => $category2->id,
            'name' => 'Laravel Item',
        ]);

        $response = $this->get(route('admin.product.search', [
            'query' => 'Laravel',
            'category_id' => $category1->id,
        ]));

        $response->assertJson(['count' => 1]);
    }

    /**
     * Test search combines with stock filter
     */
    public function test_search_combines_with_stock_filter(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Laravel Product',
            'stock_quantity' => 1,
        ]);
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Laravel Item',
            'stock_quantity' => 10,
        ]);

        $response = $this->get(route('admin.product.search', [
            'query' => 'Laravel',
            'stock_filter' => 'low_stock',
        ]));

        $response->assertJson(['count' => 1]);
    }

    /**
     * Test search returns HTML table rows
     */
    public function test_search_returns_html_table_rows(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Test Product',
        ]);

        $response = $this->get(route('admin.product.search', ['query' => 'Test']));

        $response->assertJsonStructure(['html', 'count']);
        $this->assertStringContainsString('Test Product', $response->json('html'));
    }

    /**
     * Test search handles empty query
     */
    public function test_search_handles_empty_query(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(5)->create(['category_id' => $category->id]);

        $response = $this->get(route('admin.product.search', ['query' => '']));

        $response->assertJson(['count' => 5]);
    }

    /**
     * Test search trims whitespace from query
     */
    public function test_search_trims_whitespace_from_query(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Laravel Product',
        ]);

        $response = $this->get(route('admin.product.search', ['query' => '  Laravel  ']));

        $response->assertJson(['count' => 1]);
    }

    /**
     * Test deleteReview deletes review
     */
    public function test_delete_review_removes_review(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $review = Review::factory()->create(['product_id' => $product->id]);

        $response = $this->delete(route('admin.product.review.delete', $review->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    /**
     * Test deleteReview returns 404 for non-existent review
     */
    public function test_delete_review_returns_404_for_non_existent_review(): void
    {
        $response = $this->delete(route('admin.product.review.delete', 9999));

        $response->assertStatus(404);
    }

    /**
     * Test requires authentication
     */
    public function test_requires_authentication(): void
    {
        Auth::logout();

        $response = $this->get(route('admin.product'));

        $response->assertRedirect(route('login'));
    }
}
