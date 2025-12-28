<?php

namespace Tests\Feature\Admin\Category;

use App\Models\Product\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CategoryCRUDTest extends TestCase
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
    public function admin_can_view_categories_list()
    {
        Category::factory()->count(5)->create();
        
        $response = $this->actingAs($this->admin)
            ->get(route('admin.category'));
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.categories.index');
    }

    /**
     * @test
     */
    public function admin_can_create_category()
    {
        Storage::fake('public');
        
        // Create a real temp file instead of using fake()->image() which requires GD
        $file = UploadedFile::fake()->create('category.jpg', 100, 'image/jpeg');
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.category.store'), [
                'name' => 'Test Category',
                'description' => 'Test description',
                'image' => $file
            ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category'
        ]);
    }

    /**
     * @test
     */
    public function admin_can_update_category()
    {
        $category = Category::factory()->create(['name' => 'Old Name']);
        
        $response = $this->actingAs($this->admin)
            ->put(route('admin.category.update', $category), [
                'name' => 'Updated Name',
                'description' => 'Updated description'
            ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Name'
        ]);
    }

    /**
     * @test
     */
    public function admin_can_delete_category()
    {
        $category = Category::factory()->create();
        
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.category.destroy', $category));
        
        $response->assertStatus(200)
            ->assertJson(['success' => true]);
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id
        ]);
    }

    /**
     * @test
     */
    public function non_admin_cannot_access_category_management()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $response = $this->actingAs($user)
            ->get(route('admin.category'));
        
        $response->assertStatus(403);
    }
}
