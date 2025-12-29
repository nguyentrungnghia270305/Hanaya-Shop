<?php

namespace Tests\Unit\App\Models\Product;

use App\Models\Product\Category;
use App\Models\Product\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function category_can_be_created_with_required_fields()
    {
        $category = Category::factory()->create([
            'name' => 'Flowers',
            'description' => 'Fresh flowers collection',
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Flowers',
            'description' => 'Fresh flowers collection',
        ]);
    }

    /** @test */
    public function category_has_products_relationship()
    {
        $category = Category::factory()->create();
        $products = Product::factory()->count(5)->create(['category_id' => $category->id]);

        $this->assertCount(5, $category->product);
        $this->assertInstanceOf(Product::class, $category->product->first());
    }

    /** @test */
    public function category_can_have_image_path()
    {
        $category = Category::factory()->create([
            'image_path' => '/images/categories/flowers.jpg',
        ]);

        $this->assertEquals('/images/categories/flowers.jpg', $category->image_path);
    }

    /** @test */
    public function category_timestamps_are_managed_automatically()
    {
        $category = Category::factory()->create();

        $this->assertNotNull($category->created_at);
        $this->assertNotNull($category->updated_at);
    }

    /** @test */
    public function category_can_be_updated()
    {
        $category = Category::factory()->create(['name' => 'Old Name']);

        $category->update(['name' => 'New Name']);

        $this->assertEquals('New Name', $category->fresh()->name);
    }

    /** @test */
    public function category_has_fillable_attributes()
    {
        $data = [
            'name' => 'Test Category',
            'description' => 'Test Description',
            'image_path' => '/test/path.jpg',
        ];

        $category = Category::create($data);

        $this->assertEquals('Test Category', $category->name);
        $this->assertEquals('Test Description', $category->description);
    }
}
