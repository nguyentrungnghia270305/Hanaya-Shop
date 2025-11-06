<?php

namespace Tests\Feature\Http\Controllers\Admin;
use Tests\TestCase;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_displays_the_posts_index_page()
    {
        $response = $this->get(route('admin.posts.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.posts.index');
    }

    /** @test */
    public function it_displays_the_create_post_page()
    {
        $response = $this->get(route('admin.posts.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.posts.create');
    }

    /** @test */
    public function it_stores_a_new_post()
    {
        $data = [
            'title' => 'New Post',
            'content' => 'Post Content',
        ];

        $response = $this->post(route('admin.posts.store'), $data);

        $response->assertRedirect(route('admin.posts.index'));
        $this->assertDatabaseHas('posts', ['title' => 'New Post']);
    }
}
