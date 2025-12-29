<?php

namespace Tests\Unit\App\Controllers\Admin;

use App\Models\Product\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CategoriesControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create and authenticate an admin user
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin);

        // Fake storage for image uploads
        Storage::fake('public');
    }

    /**
     * Test index method displays paginated categories
     */
    public function test_index_displays_paginated_categories(): void
    {
        Category::factory()->count(25)->create();

        $response = $this->get(route('admin.category'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.categories.index');
        $response->assertViewHas('categories');
        
        $categories = $response->viewData('categories');
        $this->assertEquals(20, $categories->perPage());
    }

    /**
     * Test index method works with no categories
     */
    public function test_index_works_with_no_categories(): void
    {
        $response = $this->get(route('admin.category'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.categories.index');
    }

    /**
     * Test create method displays category creation form
     */
    public function test_create_displays_form(): void
    {
        $response = $this->get(route('admin.category.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.categories.create');
    }

    /**
     * Test store method creates category successfully
     */
    public function test_store_creates_category_successfully(): void
    {
        Cache::shouldReceive('forget')->with('admin_categories_all')->once();

        $data = [
            'name' => 'Test Category',
            'description' => 'Test Description',
        ];

        $response = $this->post(route('admin.category.store'), $data);

        $response->assertRedirect(route('admin.category'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
            'description' => 'Test Description',
        ]);
    }

    /**
     * Test store method with image upload
     */
    public function test_store_with_image_upload(): void
    {
        if (!function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD extension is not installed.');
        }
        
        Cache::shouldReceive('forget')->with('admin_categories_all')->once();

        $image = UploadedFile::fake()->image('category.jpg', 800, 600);

        $data = [
            'name' => 'Category with Image',
            'description' => 'Test Description',
            'image' => $image,
        ];

        $response = $this->post(route('admin.category.store'), $data);

        $response->assertRedirect(route('admin.category'));
        
        $category = Category::where('name', 'Category with Image')->first();
        $this->assertNotNull($category);
        $this->assertNotNull($category->image_path);
        $this->assertStringEndsWith('.jpg', $category->image_path);
    }

    /**
     * Test store method uses default image when no image uploaded
     */
    public function test_store_uses_default_image_when_no_image(): void
    {
        Cache::shouldReceive('forget')->with('admin_categories_all')->once();

        $data = [
            'name' => 'Category No Image',
            'description' => 'Test Description',
        ];

        $response = $this->post(route('admin.category.store'), $data);

        $category = Category::where('name', 'Category No Image')->first();
        $this->assertEquals('fixed_resources/not_found.jpg', $category->image_path);
    }

    /**
     * Test store validation requires name
     */
    public function test_store_validation_requires_name(): void
    {
        $data = [
            'description' => 'Test Description',
        ];

        $response = $this->post(route('admin.category.store'), $data);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Test store validation requires unique name
     */
    public function test_store_validation_requires_unique_name(): void
    {
        Category::factory()->create(['name' => 'Existing Category']);

        $data = [
            'name' => 'Existing Category',
            'description' => 'Test Description',
        ];

        $response = $this->post(route('admin.category.store'), $data);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Test store validation rejects invalid image types
     */
    public function test_store_validation_rejects_invalid_image_type(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $data = [
            'name' => 'Test Category',
            'description' => 'Test Description',
            'image' => $file,
        ];

        $response = $this->post(route('admin.category.store'), $data);

        $response->assertSessionHasErrors('image');
    }

    /**
     * Test store validation rejects oversized images
     */
    public function test_store_validation_rejects_oversized_image(): void
    {
        if (!function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD extension is not installed.');
        }
        
        $image = UploadedFile::fake()->image('large.jpg')->size(3000);

        $data = [
            'name' => 'Test Category',
            'description' => 'Test Description',
            'image' => $image,
        ];

        $response = $this->post(route('admin.category.store'), $data);

        $response->assertSessionHasErrors('image');
    }

    /**
     * Test store clears cache after creation
     */
    public function test_store_clears_cache(): void
    {
        Cache::shouldReceive('forget')
            ->once()
            ->with('admin_categories_all');

        $data = [
            'name' => 'Test Category',
            'description' => 'Test Description',
        ];

        $this->post(route('admin.category.store'), $data);
    }

    /**
     * Test edit method displays category edit form
     */
    public function test_edit_displays_form_with_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->get(route('admin.category.edit', $category->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.categories.edit');
        $response->assertViewHas('category', $category);
    }

    /**
     * Test edit method returns 404 for non-existent category
     */
    public function test_edit_returns_404_for_non_existent_category(): void
    {
        $response = $this->get(route('admin.category.edit', 999));

        $response->assertStatus(404);
    }

    /**
     * Test update method updates category successfully
     */
    public function test_update_updates_category_successfully(): void
    {
        Cache::shouldReceive('forget')->with('admin_categories_all')->once();

        $category = Category::factory()->create([
            'name' => 'Old Name',
            'description' => 'Old Description',
        ]);

        $data = [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
        ];

        $response = $this->put(route('admin.category.update', $category->id), $data);

        $response->assertRedirect(route('admin.category'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Name',
            'description' => 'Updated Description',
        ]);
    }

    /**
     * Test update method with new image upload
     */
    public function test_update_with_new_image(): void
    {
        if (!function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD extension is not installed.');
        }
        
        Cache::shouldReceive('forget')->with('admin_categories_all')->once();

        $category = Category::factory()->create([
            'image_path' => 'old_image.jpg',
        ]);

        $newImage = UploadedFile::fake()->image('new_image.jpg');

        $data = [
            'name' => $category->name,
            'description' => $category->description,
            'image' => $newImage,
        ];

        $response = $this->put(route('admin.category.update', $category->id), $data);

        $response->assertRedirect(route('admin.category'));
        
        $category->refresh();
        $this->assertStringEndsWith('.jpg', $category->image_path);
        $this->assertNotEquals('old_image.jpg', $category->image_path);
    }

    /**
     * Test update validation allows same name for same category
     */
    public function test_update_allows_same_name_for_same_category(): void
    {
        Cache::shouldReceive('forget')->with('admin_categories_all')->once();

        $category = Category::factory()->create(['name' => 'Test Category']);

        $data = [
            'name' => 'Test Category',
            'description' => 'Updated Description',
        ];

        $response = $this->put(route('admin.category.update', $category->id), $data);

        $response->assertRedirect(route('admin.category'));
        $response->assertSessionHasNoErrors();
    }

    /**
     * Test update validation rejects duplicate name from other category
     */
    public function test_update_rejects_duplicate_name_from_other_category(): void
    {
        $category1 = Category::factory()->create(['name' => 'Category 1']);
        $category2 = Category::factory()->create(['name' => 'Category 2']);

        $data = [
            'name' => 'Category 1',
            'description' => 'Test',
        ];

        $response = $this->put(route('admin.category.update', $category2->id), $data);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Test destroy method deletes category successfully
     */
    public function test_destroy_deletes_category_successfully(): void
    {
        Cache::shouldReceive('forget')->with('admin_categories_all')->once();
        Log::shouldReceive('debug')->zeroOrMoreTimes();
        Log::shouldReceive('info')->zeroOrMoreTimes();
        Log::shouldReceive('error')->zeroOrMoreTimes();

        // Create category với default image path để tránh file operations
        $category = Category::factory()->create([
            'image_path' => 'fixed_resources/not_found.jpg' // Default image, không cần xóa
        ]);

        $response = $this->delete(route('admin.category.destroy', $category->id));
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    /**
     * Test destroy method returns 404 for non-existent category
     */
    public function test_destroy_returns_404_for_non_existent_category(): void
    {
        $response = $this->delete(route('admin.category.destroy', 999));

        $response->assertStatus(404);
    }

    /**
     * Test destroy clears cache after deletion
     */
    public function test_destroy_clears_cache(): void
    {
        Cache::shouldReceive('forget')
            ->once()
            ->with('admin_categories_all');

        $category = Category::factory()->create();

        $this->delete(route('admin.category.destroy', $category->id));
    }

    /**
     * Test search method finds categories by name
     */
    public function test_search_finds_categories_by_name(): void
    {
        Category::factory()->create(['name' => 'Flowers']);
        Category::factory()->create(['name' => 'Roses']);
        Category::factory()->create(['name' => 'Trees']);

        $response = $this->get(route('admin.category.search', ['query' => 'Flower']));

        $response->assertStatus(200);
        $response->assertJson([]);
        $this->assertStringContainsString('Flowers', $response->getContent());
    }

    /**
     * Test search method finds categories by description
     */
    public function test_search_finds_categories_by_description(): void
    {
        Category::factory()->create([
            'name' => 'Category 1',
            'description' => 'Beautiful flowers',
        ]);
        Category::factory()->create([
            'name' => 'Category 2',
            'description' => 'Green plants',
        ]);

        $response = $this->get(route('admin.category.search', ['query' => 'beautiful']));

        $response->assertStatus(200);
        $this->assertStringContainsString('Category 1', $response->getContent());
    }

    /**
     * Test search with empty query returns all categories
     */
    public function test_search_with_empty_query_returns_all(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->get(route('admin.category.search', ['query' => '']));

        $response->assertStatus(200);
    }

    /**
     * Test search returns JSON response
     */
    public function test_search_returns_json_response(): void
    {
        $response = $this->get(route('admin.category.search', ['query' => 'test']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
    }

    /**
     * Test show method returns category details view
     */
    public function test_show_returns_category_details_view(): void
    {
        $category = Category::factory()->create();

        $response = $this->get(route('admin.category.show', $category->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.categories.show');
        $response->assertViewHas('category', $category);
    }

    /**
     * Test show method returns JSON for AJAX requests
     */
    public function test_show_returns_json_for_ajax_requests(): void
    {
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'description' => 'Test Description',
            'image_path' => 'test.jpg',
        ]);

        $response = $this->getJson(route('admin.category.show', $category->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'id' => $category->id,
            'name' => 'Test Category',
            'description' => 'Test Description',
        ]);
        $response->assertJsonStructure([
            'success',
            'id',
            'name',
            'description',
            'image_path',
        ]);
    }

    /**
     * Test show method with XMLHttpRequest header
     */
    public function test_show_with_xml_http_request_header(): void
    {
        $category = Category::factory()->create();

        $response = $this->get(
            route('admin.category.show', $category->id),
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /**
     * Test show method returns 404 for non-existent category
     */
    public function test_show_returns_404_for_non_existent_category(): void
    {
        $response = $this->get(route('admin.category.show', 999));

        $response->assertStatus(404);
    }

    /**
     * Test all methods require authentication
     */
    public function test_routes_require_authentication(): void
    {
        Auth::logout();

        $category = Category::factory()->create();

        $this->get(route('admin.category'))->assertRedirect(route('login'));
        $this->get(route('admin.category.create'))->assertRedirect(route('login'));
        $this->post(route('admin.category.store'), [])->assertRedirect(route('login'));
        $this->get(route('admin.category.edit', $category->id))->assertRedirect(route('login'));
        $this->put(route('admin.category.update', $category->id), [])->assertRedirect(route('login'));
        $this->delete(route('admin.category.destroy', $category->id))->assertRedirect(route('login'));
    }

    /**
     * Test store with nullable description
     */
    public function test_store_with_nullable_description(): void
    {
        Cache::shouldReceive('forget')->with('admin_categories_all')->once();

        $data = [
            'name' => 'Category Without Description',
        ];

        $response = $this->post(route('admin.category.store'), $data);

        $response->assertRedirect(route('admin.category'));
        
        $this->assertDatabaseHas('categories', [
            'name' => 'Category Without Description',
            'description' => null,
        ]);
    }

    /**
     * Test update with nullable description
     */
    public function test_update_with_nullable_description(): void
    {
        Cache::shouldReceive('forget')->with('admin_categories_all')->once();

        $category = Category::factory()->create(['description' => 'Old Description']);

        $data = [
            'name' => $category->name,
            'description' => null,
        ];

        $response = $this->put(route('admin.category.update', $category->id), $data);

        $response->assertRedirect(route('admin.category'));
        
        $category->refresh();
        $this->assertNull($category->description);
    }

    /**
     * Test show returns default image path when category has no image
     */
    public function test_show_json_returns_default_image_path(): void
    {
        $category = Category::factory()->create(['image_path' => null]);

        $response = $this->getJson(route('admin.category.show', $category->id));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'image_path' => asset('images/categories/fixed_resources/not_found.jpg'),
        ]);
    }
}
