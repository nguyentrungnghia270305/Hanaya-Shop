<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUploadControllerEnhancedTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected string $uploadPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->uploadPath = public_path('images/posts');

        // Ensure clean state
        if (File::exists($this->uploadPath)) {
            File::cleanDirectory($this->uploadPath);
        }
    }

    protected function tearDown(): void
    {
        // Cleanup uploaded files after each test
        if (File::exists($this->uploadPath)) {
            File::cleanDirectory($this->uploadPath);
        }

        parent::tearDown();
    }

    // ===== CKEditor Upload Tests =====

    public function test_admin_can_upload_ckeditor_image(): void
    {
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['url']);

        $url = $response->json('url');
        $this->assertStringContainsString('images/posts/post_content_', $url);
    }

    public function test_ckeditor_upload_creates_directory_if_not_exists(): void
    {
        if (File::exists($this->uploadPath)) {
            File::deleteDirectory($this->uploadPath);
        }

        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        $response->assertStatus(200);
        $this->assertTrue(File::exists($this->uploadPath));
    }

    public function test_ckeditor_upload_validates_file_is_required(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.ckeditor.image'), []);

        $response->assertStatus(500)
            ->assertJsonStructure(['error']);
    }

    public function test_ckeditor_upload_validates_file_is_image(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        $response->assertStatus(500)
            ->assertJsonStructure(['error']);
    }

    public function test_ckeditor_upload_validates_file_mime_types(): void
    {
        $file = UploadedFile::fake()->create('test.bmp', 100, 'image/bmp');

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        $response->assertStatus(500)
            ->assertJsonStructure(['error']);
    }

    public function test_ckeditor_upload_validates_max_file_size(): void
    {
        $file = UploadedFile::fake()->create('huge.jpg', 11000, 'image/jpeg'); // 11MB

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        $response->assertStatus(500)
            ->assertJsonStructure(['error']);
    }

    public function test_ckeditor_upload_accepts_jpeg(): void
    {
        $file = UploadedFile::fake()->create('test.jpeg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        $response->assertStatus(200);
    }

    public function test_ckeditor_upload_accepts_png(): void
    {
        $file = UploadedFile::fake()->create('test.png', 100, 'image/png');

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        $response->assertStatus(200);
    }

    public function test_ckeditor_upload_accepts_gif(): void
    {
        $file = UploadedFile::fake()->create('test.gif', 100, 'image/gif');

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        $response->assertStatus(200);
    }

    public function test_ckeditor_upload_accepts_webp(): void
    {
        $file = UploadedFile::fake()->create('test.webp', 100, 'image/webp');

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        $response->assertStatus(200);
    }

    public function test_ckeditor_upload_generates_unique_filename(): void
    {
        $file1 = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');
        $file2 = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response1 = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.ckeditor.image'), ['upload' => $file1]);

        $response2 = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.ckeditor.image'), ['upload' => $file2]);

        $url1 = $response1->json('url');
        $url2 = $response2->json('url');

        $this->assertNotEquals($url1, $url2);
    }

    // ===== Post Image Upload Tests =====

    public function test_admin_can_upload_post_featured_image(): void
    {
        $file = UploadedFile::fake()->create('featured.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.post.image'), [
                'image' => $file,
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['url', 'filename']);

        $url = $response->json('url');
        $this->assertStringContainsString('images/posts/post_featured_', $url);
    }

    public function test_post_image_upload_validates_file_is_required(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.post.image'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('image');
    }

    public function test_post_image_upload_validates_file_is_image(): void
    {
        $file = UploadedFile::fake()->create('document.txt', 100);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.post.image'), [
                'image' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('image');
    }

    public function test_post_image_upload_validates_max_file_size(): void
    {
        $file = UploadedFile::fake()->create('large.jpg', 11000, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.post.image'), [
                'image' => $file,
            ]);

        $response->assertStatus(422);
    }

    public function test_post_image_upload_creates_directory_if_not_exists(): void
    {
        if (File::exists($this->uploadPath)) {
            File::deleteDirectory($this->uploadPath);
        }

        $file = UploadedFile::fake()->create('featured.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.post.image'), [
                'image' => $file,
            ]);

        $response->assertStatus(200);
        $this->assertTrue(File::exists($this->uploadPath));
    }

    public function test_post_image_upload_returns_filename(): void
    {
        $file = UploadedFile::fake()->create('featured.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.post.image'), [
                'image' => $file,
            ]);

        $response->assertStatus(200);
        $filename = $response->json('filename');

        $this->assertNotNull($filename);
        $this->assertStringStartsWith('post_featured_', $filename);
    }

    // ===== TinyMCE Upload Tests =====

    public function test_admin_can_upload_tinymce_image(): void
    {
        $file = UploadedFile::fake()->create('content.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.tinymce.image'), [
                'file' => $file,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['location']);

        $location = $response->json('location');
        $this->assertStringContainsString('images/posts/tinymce_content_', $location);
    }

    public function test_tinymce_upload_validates_file_is_required(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.tinymce.image'), []);

        $response->assertStatus(500)
            ->assertJsonStructure(['error']);
    }

    public function test_tinymce_upload_validates_file_is_image(): void
    {
        $file = UploadedFile::fake()->create('script.js', 100);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.tinymce.image'), [
                'file' => $file,
            ]);

        $response->assertStatus(500)
            ->assertJsonStructure(['error']);
    }

    public function test_tinymce_upload_validates_max_file_size(): void
    {
        $file = UploadedFile::fake()->create('massive.jpg', 12000, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.tinymce.image'), [
                'file' => $file,
            ]);

        $response->assertStatus(500)
            ->assertJsonStructure(['error']);
    }

    public function test_tinymce_upload_creates_directory_if_not_exists(): void
    {
        if (File::exists($this->uploadPath)) {
            File::deleteDirectory($this->uploadPath);
        }

        $file = UploadedFile::fake()->create('content.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.tinymce.image'), [
                'file' => $file,
            ]);

        $response->assertStatus(200);
        $this->assertTrue(File::exists($this->uploadPath));
    }

    public function test_tinymce_upload_accepts_all_supported_formats(): void
    {
        $formats = [
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];

        foreach ($formats as $ext => $mime) {
            $file = UploadedFile::fake()->create("test.{$ext}", 100, $mime);

            $response = $this->actingAs($this->admin)
                ->postJson(route('admin.upload.tinymce.image'), [
                    'file' => $file,
                ]);

            $response->assertStatus(200);
        }
    }

    // ===== Authorization Tests =====

    public function test_guest_cannot_upload_ckeditor_image(): void
    {
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->postJson(route('admin.upload.ckeditor.image'), [
            'upload' => $file,
        ]);

        $response->assertStatus(401);
    }

    public function test_guest_cannot_upload_post_image(): void
    {
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->postJson(route('admin.upload.post.image'), [
            'image' => $file,
        ]);

        $response->assertStatus(401);
    }

    public function test_guest_cannot_upload_tinymce_image(): void
    {
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->postJson(route('admin.upload.tinymce.image'), [
            'file' => $file,
        ]);

        $response->assertStatus(401);
    }

    public function test_regular_user_cannot_upload_images(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)
            ->postJson(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        $response->assertStatus(403);
    }

    // ===== File Storage Tests =====

    public function test_uploaded_files_are_stored_in_correct_directory(): void
    {
        $file = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.ckeditor.image'), [
                'upload' => $file,
            ]);

        $response->assertStatus(200);

        $url = $response->json('url');
        $filename = basename($url);
        $filePath = $this->uploadPath.'/'.$filename;

        $this->assertTrue(File::exists($filePath));
    }

    public function test_multiple_uploads_do_not_overwrite_files(): void
    {
        $file1 = UploadedFile::fake()->create('test1.jpg', 100, 'image/jpeg');
        $file2 = UploadedFile::fake()->create('test2.jpg', 100, 'image/jpeg');

        $response1 = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.ckeditor.image'), ['upload' => $file1]);

        $response2 = $this->actingAs($this->admin)
            ->postJson(route('admin.upload.ckeditor.image'), ['upload' => $file2]);

        $url1 = $response1->json('url');
        $url2 = $response2->json('url');

        $filename1 = basename($url1);
        $filename2 = basename($url2);

        $this->assertTrue(File::exists($this->uploadPath.'/'.$filename1));
        $this->assertTrue(File::exists($this->uploadPath.'/'.$filename2));
    }
}
