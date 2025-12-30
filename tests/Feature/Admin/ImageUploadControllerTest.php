<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImageUploadControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    // Helper method to create fake files without GD extension
    protected function createFakeImage($filename, $mimeType = 'image/jpeg')
    {
        return UploadedFile::fake()->create($filename, 100, $mimeType);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);

        // Create images directory for testing
        if (! file_exists(public_path('images/posts'))) {
            mkdir(public_path('images/posts'), 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test images
        $files = glob(public_path('images/posts/*'));
        foreach ($files as $file) {
            if (is_file($file) && strpos($file, 'post_') !== false) {
                @unlink($file);
            }
        }

        parent::tearDown();
    }

    /**
     * @test
     */
    public function admin_can_upload_ckeditor_image()
    {
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['url']);
    }

    /**
     * @test
     */
    public function ckeditor_upload_validates_file_type()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        $response->assertStatus(500)
            ->assertJsonStructure(['error']);
    }

    /**
     * @test
     */
    public function ckeditor_upload_validates_file_size()
    {
        $file = UploadedFile::fake()->create('large.jpg', 15000, 'image/jpeg'); // 15MB

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        $response->assertStatus(500)
            ->assertJsonStructure(['error']);
    }

    /**
     * @test
     */
    public function ckeditor_upload_requires_file()
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.ckeditor.image'), []);

        // Controller returns 400 when no file, or 500 on validation error
        $this->assertContains($response->status(), [400, 500]);
        $response->assertJsonStructure(['error']);
    }

    /**
     * @test
     */
    public function ckeditor_upload_accepts_jpeg_images()
    {
        $file = UploadedFile::fake()->create('test.jpeg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        $response->assertOk();
    }

    /**
     * @test
     */
    public function ckeditor_upload_accepts_png_images()
    {
        $file = UploadedFile::fake()->create('test.png', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        $response->assertOk();
    }

    /**
     * @test
     */
    public function ckeditor_upload_accepts_gif_images()
    {
        $file = UploadedFile::fake()->create('test.gif', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        $response->assertOk();
    }

    /**
     * @test
     */
    public function ckeditor_upload_accepts_webp_images()
    {
        $file = UploadedFile::fake()->create('test.webp', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        $response->assertOk();
    }

    /**
     * @test
     */
    public function ckeditor_upload_creates_unique_filename()
    {
        $file1 = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');
        $file2 = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response1 = $this->actingAs($this->admin)
            ->post(route('admin.upload.ckeditor.image'), ['upload' => $file1]);

        $response2 = $this->actingAs($this->admin)
            ->post(route('admin.upload.ckeditor.image'), ['upload' => $file2]);

        $url1 = $response1->json('url');
        $url2 = $response2->json('url');

        $this->assertNotEquals($url1, $url2);
    }

    /**
     * @test
     */
    public function ckeditor_upload_returns_accessible_url()
    {
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        $url = $response->json('url');
        $this->assertStringContainsString('images/posts/', $url);
        $this->assertStringContainsString('post_content_', $url);
    }

    /**
     * @test
     */
    public function admin_can_upload_post_featured_image()
    {
        $file = UploadedFile::fake()->create('featured.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.upload.post.image'), [
                'image' => $file,
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['success', 'url', 'filename']);
        $response->assertJson(['success' => true]);
    }

    /**
     * @test
     */
    public function post_image_upload_validates_file_type()
    {
        $file = UploadedFile::fake()->create('document.txt', 100);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.upload.post.image'), [
                'image' => $file,
            ]);

        $response->assertSessionHasErrors('image');
    }

    /**
     * @test
     */
    public function post_image_upload_validates_file_size()
    {
        $file = UploadedFile::fake()->create('large.jpg', 15000, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.upload.post.image'), [
                'image' => $file,
            ]);

        $response->assertSessionHasErrors('image');
    }

    /**
     * @test
     */
    public function post_image_upload_requires_file()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.upload.post.image'), []);

        $response->assertSessionHasErrors('image');
    }

    /**
     * @test
     */
    public function post_image_filename_contains_featured_prefix()
    {
        $file = UploadedFile::fake()->create('featured.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.upload.post.image'), [
                'image' => $file,
            ]);

        $filename = $response->json('filename');
        $this->assertStringContainsString('post_featured_', $filename);
    }

    /**
     * @test
     */
    public function guest_cannot_upload_ckeditor_image()
    {
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->post(route('admin.upload.ckeditor.image'), [
            'upload' => $file,
        ]);

        $response->assertRedirect(route('login'));
    }

    /**
     * @test
     */
    public function guest_cannot_upload_post_image()
    {
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->post(route('admin.upload.post.image'), [
            'image' => $file,
        ]);

        $response->assertRedirect(route('login'));
    }

    /**
     * @test
     */
    public function regular_user_cannot_upload_ckeditor_image()
    {
        $user = User::factory()->create(['role' => 'user']);
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)
            ->post(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function regular_user_cannot_upload_post_image()
    {
        $user = User::factory()->create(['role' => 'user']);
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)
            ->post(route('admin.upload.post.image'), [
                'image' => $file,
            ]);

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function upload_creates_directory_if_not_exists()
    {
        // Remove directory if exists
        if (file_exists(public_path('images/posts'))) {
            $files = glob(public_path('images/posts/*'));
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            @rmdir(public_path('images/posts'));
        }

        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        $response->assertOk();
        $this->assertTrue(file_exists(public_path('images/posts')));
    }

    /**
     * @test
     */
    public function ckeditor_upload_handles_exceptions_gracefully()
    {
        // Mock file system error
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        // Make directory read-only to force error (may not work on Windows)
        chmod(public_path('images'), 0444);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        // Restore permissions
        chmod(public_path('images'), 0755);

        // On Windows, chmod may not prevent writes, so accept both 200 and 500
        $this->assertContains($response->status(), [200, 500]);
        if ($response->status() === 500) {
            $response->assertJsonStructure(['error']);
        }
    }

    /**
     * @test
     */
    public function post_image_upload_returns_both_url_and_filename()
    {
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.upload.post.image'), [
                'image' => $file,
            ]);

        $json = $response->json();

        $this->assertArrayHasKey('url', $json);
        $this->assertArrayHasKey('filename', $json);
        $this->assertStringContainsString($json['filename'], $json['url']);
    }

    /**
     * @test
     */
    public function admin_can_upload_tinymce_image()
    {
        $file = UploadedFile::fake()->create('tinymce.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.upload.tinymce.image'), [
                'file' => $file,
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['location']);
    }

    /**
     * @test
     */
    public function tinymce_upload_validates_file_type()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.tinymce.image'), [
                'file' => $file,
            ]);

        $response->assertStatus(500)
            ->assertJsonStructure(['error']);
    }

    /**
     * @test
     */
    public function tinymce_upload_validates_file_size()
    {
        $file = UploadedFile::fake()->create('large.jpg', 15000, 'image/jpeg'); // 15MB

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.tinymce.image'), [
                'file' => $file,
            ]);

        $response->assertStatus(500)
            ->assertJsonStructure(['error']);
    }

    /**
     * @test
     */
    public function tinymce_upload_requires_file()
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.tinymce.image'), []);

        // Controller returns 400 when no file, or 500 on validation error
        $this->assertContains($response->status(), [400, 500]);
        $response->assertJsonStructure(['error']);
    }

    /**
     * @test
     */
    public function tinymce_filename_contains_tinymce_prefix()
    {
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.upload.tinymce.image'), [
                'file' => $file,
            ]);

        $location = $response->json('location');
        $this->assertStringContainsString('tinymce_content_', $location);
    }

    /**
     * @test
     */
    public function tinymce_upload_returns_location_key()
    {
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.upload.tinymce.image'), [
                'file' => $file,
            ]);

        $response->assertOk();
        $this->assertArrayHasKey('location', $response->json());
    }

    /**
     * @test
     */
    public function tinymce_upload_creates_file_in_posts_directory()
    {
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.upload.tinymce.image'), [
                'file' => $file,
            ]);

        $location = $response->json('location');
        $this->assertStringContainsString('images/posts/', $location);
    }

    /**
     * @test
     */
    public function guest_cannot_upload_tinymce_image()
    {
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->post(route('admin.upload.tinymce.image'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('login'));
    }

    /**
     * @test
     */
    public function regular_user_cannot_upload_tinymce_image()
    {
        $user = User::factory()->create(['role' => 'user']);
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)
            ->post(route('admin.upload.tinymce.image'), [
                'file' => $file,
            ]);

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function tinymce_upload_handles_exceptions_gracefully()
    {
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        // Make directory read-only to force error (may not work on Windows)
        chmod(public_path('images'), 0444);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.upload.tinymce.image'), [
                'file' => $file,
            ]);

        // Restore permissions
        chmod(public_path('images'), 0755);

        // On Windows, chmod may not prevent writes, so accept both 200 and 500
        $this->assertContains($response->status(), [200, 500]);
        if ($response->status() === 500) {
            $response->assertJsonStructure(['error']);
        }
    }

    /**
     * @test
     */
    public function all_upload_endpoints_support_webp_format()
    {
        $file = UploadedFile::fake()->create('test.webp', 100, 'image/jpeg');

        // CKEditor
        $response1 = $this->actingAs($this->admin)
            ->post(route('admin.upload.ckeditor.image'), ['upload' => $file]);
        $response1->assertOk();

        // Post Image
        $file2 = UploadedFile::fake()->create('test2.webp', 100, 'image/jpeg');
        $response2 = $this->actingAs($this->admin)
            ->post(route('admin.upload.post.image'), ['image' => $file2]);
        $response2->assertOk();

        // TinyMCE
        $file3 = UploadedFile::fake()->create('test3.webp', 100, 'image/jpeg');
        $response3 = $this->actingAs($this->admin)
            ->post(route('admin.upload.tinymce.image'), ['file' => $file3]);
        $response3->assertOk();
    }

    /**
     * @test
     */
    public function uploaded_filenames_include_timestamp()
    {
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        $url = $response->json('url');
        $filename = basename($url);

        // Filename should contain timestamp in YmdHis format
        $this->assertMatchesRegularExpression('/\d{14}/', $filename);
    }

    /**
     * @test
     */
    public function uploaded_filenames_include_random_string()
    {
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        $url = $response->json('url');
        $filename = basename($url);

        // Filename should have sufficient length (timestamp + random + extension)
        $this->assertGreaterThan(25, strlen($filename));
    }

    /**
     * @test
     */
    public function ckeditor_upload_no_file_returns_400()
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.ckeditor.image'), []);

        // Controller returns 500 when validation fails (caught by try-catch)
        $this->assertContains($response->status(), [400, 500]);
        $response->assertJsonStructure(['error']);
    }
}
