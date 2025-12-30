<?php

namespace Tests\Feature\Admin\Category;

use App\Models\Product\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryValidationTest extends TestCase
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
    public function category_name_is_required()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.category.store'), [
                'description' => 'Test description',
            ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * @test
     */
    public function category_name_must_be_unique()
    {
        Category::factory()->create(['name' => 'Existing Category']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.category.store'), [
                'name' => 'Existing Category',
                'description' => 'Test description',
            ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * @test
     */
    public function category_image_must_be_valid_image_file()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.category.store'), [
                'name' => 'Test Category',
                'image' => 'not-an-image',
            ]);

        $response->assertSessionHasErrors('image');
    }

    /**
     * @test
     */
    public function category_description_is_optional()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.category.store'), [
                'name' => 'Test Category',
            ]);

        $response->assertSessionDoesntHaveErrors('description');
    }
}
