<?php

namespace Tests\Feature\Http\Controllers\Admin;

use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_displays_the_products_index_page()
    {
        $response = $this->get(route('admin.products.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.products.index');
    }

    /** @test */
    public function it_displays_the_create_product_page()
    {
        $response = $this->get(route('admin.products.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.products.create');
    }

    /** @test */
    public function it_stores_a_new_product()
    {
        $data = [
            'name' => 'New Product',
            'description' => 'Product Description',
            'price' => 99.99,
        ];

        $response = $this->post(route('admin.products.store'), $data);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', ['name' => 'New Product']);
    }
}
