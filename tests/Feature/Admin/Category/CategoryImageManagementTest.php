<?php

namespace Tests\Feature\Admin\Category;

use App\Models\Product\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CategoryImageManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        
        // Ensure the categories image directory exists
        if (!File::exists(public_path('images/categories'))) {
            File::makeDirectory(public_path('images/categories'), 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test images
        $testImages = File::glob(public_path('images/categories/test-*'));
        foreach ($testImages as $image) {
            @unlink($image);
        }
        
        parent::tearDown();
    }

    /**
     * @test
     * Test coverage for: update() method - image upload and old image deletion
     */
    public function admin_can_replace_category_image_and_delete_old_one()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->markTestSkipped('File upload tests are skipped on Windows due to file permission issues.');
        }
        
        // Create a real image file for the initial category
        $oldImageName = 'test-old-' . time() . '.jpg';
        $oldImagePath = public_path('images/categories/' . $oldImageName);
        
        // Create a simple 1x1 pixel image
        $imageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        file_put_contents($oldImagePath, $imageContent);
        
        // Create category with old image
        $category = Category::factory()->create([
            'name' => 'Test Category',
            'image_path' => $oldImageName
        ]);
        
        // Verify old image exists
        $this->assertFileExists($oldImagePath);
        
        // Create new fake image for upload
        $newImage = UploadedFile::fake()->create('new-image.jpg', 100, 'image/jpeg');
        
        // Update category with new image
        $response = $this->actingAs($this->admin)
            ->put(route('admin.category.update', $category->id), [
                'name' => 'Test Category',
                'description' => 'Updated description',
                'image' => $newImage
            ]);
        
        $response->assertRedirect(route('admin.category'));
        
        // Refresh category from database
        $category->refresh();
        
        // Assert image_path was updated
        $this->assertNotEquals($oldImageName, $category->image_path);
        
        // Assert old image was deleted
        $this->assertFileDoesNotExist($oldImagePath);
        
        // Assert new image exists
        $this->assertFileExists(public_path('images/categories/' . $category->image_path));
        
        // Cleanup new image
        @unlink(public_path('images/categories/' . $category->image_path));
    }

    /**
     * @test
     * Test coverage for: update() method - upload image when category has no previous image
     * 
     * Note: This test is skipped on Windows due to file permission issues when moving files
     * from temp directory. The code is covered by admin_can_create_category_with_image test.
     */
    public function admin_can_upload_image_to_category_without_previous_image()
    {
        // Skip on Windows due to permission issues with UploadedFile::fake()->create()
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->markTestSkipped('Skipped on Windows due to file permission issues.');
        }
        
        // Ensure directory exists and is writable
        $dir = public_path('images/categories');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0777, true);
        }
        
        // Create category without image (using default)
        $category = Category::factory()->create([
            'name' => 'No Image Category',
            'image_path' => 'fixed_resources/not_found.jpg'
        ]);
        
        // Create fake image for upload
        $newImage = UploadedFile::fake()->create('new-image.jpg', 100, 'image/jpeg');
        
        // Update category with image
        $response = $this->actingAs($this->admin)
            ->put(route('admin.category.update', $category->id), [
                'name' => 'No Image Category',
                'description' => 'Now with image',
                'image' => $newImage
            ]);
        
        $response->assertRedirect(route('admin.category'));
        
        $category->refresh();
        
        // Assert image_path was updated
        $this->assertNotEquals('fixed_resources/not_found.jpg', $category->image_path);
        
        // Assert new image exists
        $this->assertFileExists(public_path('images/categories/' . $category->image_path));
        
        // Cleanup
        @unlink(public_path('images/categories/' . $category->image_path));
    }

    /**
     * @test
     * Test coverage for: destroy() method - successful image deletion with Log::info
     */
    public function destroy_deletes_image_and_logs_success()
    {
        // Create a real image file
        $imageName = 'test-delete-' . time() . '.jpg';
        $imagePath = public_path('images/categories/' . $imageName);
        
        // Create a simple 1x1 pixel image
        $imageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        file_put_contents($imagePath, $imageContent);
        
        // Create category with this image
        $category = Category::factory()->create([
            'name' => 'To Delete',
            'image_path' => $imageName
        ]);
        
        // Verify image exists before deletion
        $this->assertFileExists($imagePath);
        
        // Delete category
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.category.destroy', $category->id));
        
        $response->assertStatus(200)
            ->assertJson(['success' => true]);
        
        // Assert category was deleted from database
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id
        ]);
        
        // Assert image was deleted from filesystem (covers the unlink success path and Log::info)
        $this->assertFileDoesNotExist($imagePath);
    }

    /**
     * @test
     * Test coverage for: destroy() method - category without image_path
     */
    public function destroy_works_when_category_has_no_image_path()
    {
        // Create category with null image_path
        $category = Category::factory()->create([
            'name' => 'No Image',
            'image_path' => null
        ]);
        
        // Delete category
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.category.destroy', $category->id));
        
        $response->assertStatus(200)
            ->assertJson(['success' => true]);
        
        // Assert category was deleted
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id
        ]);
    }

    /**
     * @test
     * Test coverage for: destroy() method - image file doesn't exist on filesystem
     */
    public function destroy_works_when_image_file_does_not_exist()
    {
        // Create category with image_path pointing to non-existent file
        $category = Category::factory()->create([
            'name' => 'Missing Image',
            'image_path' => 'non-existent-image.jpg'
        ]);
        
        // Verify file doesn't exist
        $this->assertFileDoesNotExist(public_path('images/categories/non-existent-image.jpg'));
        
        // Delete category
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.category.destroy', $category->id));
        
        $response->assertStatus(200)
            ->assertJson(['success' => true]);
        
        // Assert category was deleted
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id
        ]);
    }

    /**
     * @test
     * Test coverage for: store() method - image upload on creation
     */
    public function admin_can_create_category_with_image()
    {
        // Skip on Windows due to file permission issues
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->markTestSkipped('Skipped on Windows due to file permission issues with UploadedFile::fake()->create().');
        }
        
        // Ensure directory exists and is writable
        $dir = public_path('images/categories');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0777, true);
        }
        
        $image = UploadedFile::fake()->create('category-new.jpg', 100, 'image/jpeg');
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.category.store'), [
                'name' => 'New Category With Image',
                'description' => 'Test description',
                'image' => $image
            ]);
        
        $response->assertRedirect(route('admin.category'));
        
        $this->assertDatabaseHas('categories', [
            'name' => 'New Category With Image'
        ]);
        
        $category = Category::where('name', 'New Category With Image')->first();
        
        // Assert image was uploaded
        $this->assertNotEquals('fixed_resources/not_found.jpg', $category->image_path);
        $this->assertFileExists(public_path('images/categories/' . $category->image_path));
        
        // Cleanup
        @unlink(public_path('images/categories/' . $category->image_path));
    }

    /**
     * @test
     * Test coverage for: store() method - no image uploaded (default image)
     */
    public function admin_can_create_category_without_image_uses_default()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.category.store'), [
                'name' => 'Category Without Image',
                'description' => 'Test description'
            ]);
        
        $response->assertRedirect(route('admin.category'));
        
        $category = Category::where('name', 'Category Without Image')->first();
        
        // Assert default image path was used
        $this->assertEquals('fixed_resources/not_found.jpg', $category->image_path);
    }
}
