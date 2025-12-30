<?php

namespace Tests\Feature\Admin\Product;

use App\Models\Product\Category;
use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProductImageUploadTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Disable CSRF middleware for feature form submissions
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
        $this->admin = User::factory()->create(['role' => 'admin']);

        // Create directory if it doesn't exist
        $uploadPath = public_path('images/products');
        if (! file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up uploaded test files
        $uploadPath = public_path('images/products');
        if (file_exists($uploadPath)) {
            $files = glob($uploadPath.'/*');
            foreach ($files as $file) {
                if (is_file($file) && strpos(basename($file), '.gitkeep') === false) {
                    @unlink($file);
                }
            }
        }

        parent::tearDown();
    }

    /**
     * @test
     */
    public function admin_can_upload_product_image()
    {
        $category = Category::factory()->create();
        // Provide a minimal valid PNG content to satisfy `image` rule without GD
        $oneByOnePng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMB/6X0rG8AAAAASUVORK5CYII=');
        $file = UploadedFile::fake()->createWithContent('product.png', $oneByOnePng);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product.store'), [
                'name' => 'Test Product',
                'descriptions' => 'Test description',
                'price' => 100,
                'stock_quantity' => 50,
                'category_id' => $category->id,
                'image_url' => $file,
            ]);

        $response->assertRedirect();
    }

    /**
     * @test
     */
    public function product_image_upload_is_optional()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product.store'), [
                'name' => 'Test Product',
                'descriptions' => 'Test description',
                'price' => 100,
                'stock_quantity' => 50,
                'category_id' => $category->id,
            ]);

        $response->assertSessionDoesntHaveErrors('image_url');
    }

    /**
     * @test
     */
    public function admin_can_update_product_image()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->markTestSkipped('Skipped on Windows due to file permission issues');
        }

        $product = Product::factory()->create();

        $file = UploadedFile::fake()->create('new-product.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->put(route('admin.product.update', $product), [
                'name' => $product->name,
                'descriptions' => $product->descriptions,
                'price' => $product->price,
                'stock_quantity' => $product->stock_quantity,
                'category_id' => $product->category_id,
                'image_url' => $file,
            ]);

        $response->assertRedirect();
    }

    /**
     * @test
     */
    public function product_image_must_be_valid_image_file()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product.store'), [
                'name' => 'Test Product',
                'descriptions' => 'Test description',
                'price' => 100,
                'stock_quantity' => 50,
                'category_id' => $category->id,
                'image_url' => UploadedFile::fake()->create('document.pdf'),
            ]);

        $response->assertSessionHasErrors('image_url');
    }
}
