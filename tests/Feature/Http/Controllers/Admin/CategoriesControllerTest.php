<?php

namespace Tests\Feature\Http\Controllers\Admin;
use Tests\TestCase;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoriesControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_displays_the_categories_index_page()
    {
        $response = $this->get(route('admin.categories.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.categories.index');
    }

    /** @test */
    public function it_displays_the_create_category_page()
    {
        $response = $this->get(route('admin.categories.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.categories.create');
    }

    /** @test */
    public function it_stores_a_new_category()
    {
        $data = [
            'name' => 'New Category',
            'description' => 'Category Description',
        ];

        $response = $this->post(route('admin.categories.store'), $data);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'New Category']);
    }
}
