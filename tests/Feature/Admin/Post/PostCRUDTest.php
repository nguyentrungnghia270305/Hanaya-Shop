<?php

namespace Tests\Feature\Admin\Post;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostCRUDTest extends TestCase
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
    public function admin_can_view_posts_list()
    {
        Post::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.post.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.posts.index');
    }

    /**
     * @test
     */
    public function admin_can_create_post()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.post.store'), [
                'title' => 'Test Post',
                'content' => 'Test content for the post',
                'status' => true,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
        ]);
    }

    /**
     * @test
     */
    public function admin_can_update_post()
    {
        $post = Post::factory()->create(['title' => 'Old Title']);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.post.update', $post), [
                'title' => 'Updated Title',
                'content' => 'Updated content',
                'status' => true,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
        ]);
    }

    /**
     * @test
     */
    public function admin_can_delete_post()
    {
        $post = Post::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.post.destroy', $post));

        $response->assertRedirect();
        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    /**
     * @test
     */
    // public function admin_can_publish_unpublished_post()
    // {
    //     $post = Post::factory()->create(['status' => 'draft']);

    //     $response = $this->actingAs($this->admin)
    //         ->patch(route('admin.post.publish', $post));

    //     $response->assertRedirect();
    //     $this->assertDatabaseHas('posts', [
    //         'id' => $post->id,
    //         'status' => 'published'
    //     ]);
    // }

    /**
     * @test
     */
    public function non_admin_cannot_access_post_management()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)
            ->get(route('admin.post.index'));

        $response->assertStatus(403);
    }
}
