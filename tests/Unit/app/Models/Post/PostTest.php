<?php

namespace Tests\Unit\App\Models\Post;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function post_can_be_created_with_required_fields()
    {
        $user = User::factory()->create();

        $post = Post::factory()->create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => 'Test content',
            'user_id' => $user->id,
            'status' => 1, // 1 = published
        ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'slug' => 'test-post',
            'status' => 1,
        ]);
    }

    /** @test */
    public function post_belongs_to_author()
    {
        $user = User::factory()->create(['name' => 'Author Name']);
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $post->author);
        $this->assertEquals('Author Name', $post->author->name);
    }

    /** @test */
    public function post_can_have_featured_image()
    {
        $post = Post::factory()->create([
            'image' => 'featured-image.jpg',
        ]);

        $this->assertEquals('featured-image.jpg', $post->image);
    }

    /** @test */
    public function post_has_fillable_attributes()
    {
        $user = User::factory()->create();
        $data = [
            'title' => 'New Post',
            'slug' => 'new-post',
            'content' => 'Post content',
            'image' => 'image.jpg',
            'status' => 0, // 0 = draft
            'user_id' => $user->id,
        ];

        $post = Post::create($data);

        $this->assertEquals('New Post', $post->title);
        $this->assertEquals(0, $post->status);
    }

    /** @test */
    public function post_can_be_updated()
    {
        $post = Post::factory()->create(['status' => 0]); // 0 = draft

        $post->update(['status' => 1]); // 1 = published

        $this->assertEquals(1, $post->fresh()->status);
    }

    /** @test */
    public function user_can_have_multiple_posts()
    {
        $user = User::factory()->create();
        Post::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->posts);
    }
}
