<?php

namespace Tests\Feature\User;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected $publishedPost;

    protected $unpublishedPost;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->publishedPost = Post::factory()->create([
            'title' => 'Published Post Title',
            'content' => 'Published content here with searchable text',
            'status' => true,
            'user_id' => $this->admin->id,
        ]);

        $this->unpublishedPost = Post::factory()->create([
            'title' => 'Unpublished Post',
            'content' => 'Draft content',
            'status' => false,
            'user_id' => $this->admin->id,
        ]);
    }

    /**
     * @test
     */
    public function guest_can_view_posts_index()
    {
        $response = $this->get(route('posts.index'));

        $response->assertOk();
        $response->assertViewIs('page.posts.index');
    }

    /**
     * @test
     */
    public function posts_index_shows_only_published_posts()
    {
        $response = $this->get(route('posts.index'));

        $response->assertSee($this->publishedPost->title);
        $response->assertDontSee($this->unpublishedPost->title);
    }

    /**
     * @test
     */
    public function posts_index_includes_author_information()
    {
        $response = $this->get(route('posts.index'));

        $response->assertViewHas('posts', function ($posts) {
            return $posts->first()->relationLoaded('author');
        });
    }

    /**
     * @test
     */
    public function posts_index_paginates_results()
    {
        Post::factory()->count(15)->create(['status' => true, 'user_id' => $this->admin->id]);

        $response = $this->get(route('posts.index'));

        $response->assertViewHas('posts', function ($posts) {
            return $posts instanceof \Illuminate\Pagination\LengthAwarePaginator;
        });
    }

    /**
     * @test
     */
    public function posts_index_can_search_by_title()
    {
        $response = $this->get(route('posts.index', ['search' => 'Published Post']));

        $response->assertSee($this->publishedPost->title);
    }

    /**
     * @test
     */
    public function posts_index_can_search_by_content()
    {
        $response = $this->get(route('posts.index', ['search' => 'searchable text']));

        $response->assertSee($this->publishedPost->title);
    }

    /**
     * @test
     */
    public function posts_index_search_is_case_insensitive()
    {
        $response = $this->get(route('posts.index', ['search' => 'PUBLISHED']));

        $response->assertSee($this->publishedPost->title);
    }

    /**
     * @test
     */
    public function guest_can_view_published_post_detail()
    {
        $response = $this->get(route('posts.show', $this->publishedPost->id));

        $response->assertOk();
        $response->assertViewIs('page.posts.show');
        $response->assertSee($this->publishedPost->title);
        $response->assertSee($this->publishedPost->content);
    }

    /**
     * @test
     */
    public function post_detail_includes_author_information()
    {
        $response = $this->get(route('posts.show', $this->publishedPost->id));

        $response->assertViewHas('post', function ($post) {
            return $post->relationLoaded('author');
        });
        $response->assertSee($this->admin->name);
    }

    /**
     * @test
     */
    public function guest_cannot_view_unpublished_post()
    {
        $response = $this->get(route('posts.show', $this->unpublishedPost->id));

        $response->assertNotFound();
    }

    /**
     * @test
     */
    public function post_detail_returns_404_for_non_existent_post()
    {
        $response = $this->get(route('posts.show', 999999));

        $response->assertNotFound();
    }

    /**
     * @test
     */
    public function search_with_no_results_shows_empty_message()
    {
        $response = $this->get(route('posts.index', ['search' => 'nonexistent search term xyz']));

        $response->assertOk();
        $response->assertDontSee($this->publishedPost->title);
    }

    /**
     * @test
     */
    public function posts_index_preserves_search_parameter_in_pagination()
    {
        Post::factory()->count(15)->create([
            'title' => 'Searchable Title',
            'status' => true,
            'user_id' => $this->admin->id,
        ]);

        $response = $this->get(route('posts.index', ['search' => 'Searchable']));

        $response->assertViewHas('posts', function ($posts) {
            return str_contains($posts->appends(['search'])->links()->toHtml(), 'search=');
        });
    }

    /**
     * @test
     */
    public function posts_index_orders_by_latest_first()
    {
        // Xóa các post có sẵn từ setUp
        Post::query()->delete();

        $olderPost = Post::factory()->create([
            'title' => 'Older Post',
            'status' => true,
            'user_id' => $this->admin->id,
            'created_at' => now()->subDays(5),
        ]);

        $newerPost = Post::factory()->create([
            'title' => 'Newer Post',
            'status' => true,
            'user_id' => $this->admin->id,
            'created_at' => now()->subDay(),
        ]);

        $response = $this->get(route('posts.index'));

        $response->assertViewHas('posts', function ($posts) use ($newerPost) {
            return $posts->first()->id === $newerPost->id;
        });
    }

    /**
     * @test
     */
    public function posts_index_limits_to_10_posts_per_page()
    {
        Post::factory()->count(12)->create([
            'status' => true,
            'user_id' => $this->admin->id,
        ]);

        $response = $this->get(route('posts.index'));

        $response->assertViewHas('posts', function ($posts) {
            return $posts->count() === 10;
        });
    }

    /**
     * @test
     */
    public function posts_show_requires_published_status()
    {
        $draftPost = Post::factory()->create([
            'status' => false,
            'user_id' => $this->admin->id,
        ]);

        $response = $this->get(route('posts.show', $draftPost->id));

        $response->assertNotFound();
    }

    /**
     * @test
     */
    public function posts_show_loads_author_relationship()
    {
        $response = $this->get(route('posts.show', $this->publishedPost->id));

        $response->assertViewHas('post', function ($post) {
            return $post->relationLoaded('author');
        });
    }

    /**
     * @test
     */
    public function search_works_with_partial_matches()
    {
        Post::factory()->create([
            'title' => 'Beautiful Soap Flowers',
            'status' => true,
            'user_id' => $this->admin->id,
        ]);

        $response = $this->get(route('posts.index', ['search' => 'Soap']));

        $response->assertSee('Beautiful Soap Flowers');
    }

    /**
     * @test
     */
    public function search_searches_both_title_and_content()
    {
        $post1 = Post::factory()->create([
            'title' => 'Flowers Guide',
            'content' => 'This is about roses',
            'status' => true,
            'user_id' => $this->admin->id,
        ]);

        $post2 = Post::factory()->create([
            'title' => 'Care Tips',
            'content' => 'How to care for your flowers',
            'status' => true,
            'user_id' => $this->admin->id,
        ]);

        $response = $this->get(route('posts.index', ['search' => 'flowers']));

        $response->assertSee('Flowers Guide');
        $response->assertSee('Care Tips');
    }

    /**
     * @test
     */
    public function empty_search_shows_all_posts()
    {
        Post::factory()->count(5)->create([
            'status' => true,
            'user_id' => $this->admin->id,
        ]);

        $response = $this->get(route('posts.index', ['search' => '']));

        $response->assertOk();
        $response->assertViewHas('posts', function ($posts) {
            return $posts->count() >= 5;
        });
    }

    /**
     * @test
     */
    public function posts_index_view_is_correct()
    {
        $response = $this->get(route('posts.index'));

        $response->assertViewIs('page.posts.index');
    }

    /**
     * @test
     */
    public function posts_show_view_is_correct()
    {
        $response = $this->get(route('posts.show', $this->publishedPost->id));

        $response->assertViewIs('page.posts.show');
    }

    /**
     * @test
     */
    public function authenticated_user_can_view_posts()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('posts.index'));

        $response->assertOk();
        $response->assertSee($this->publishedPost->title);
    }

    /**
     * @test
     */
    public function admin_can_view_posts()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('posts.index'));

        $response->assertOk();
        $response->assertSee($this->publishedPost->title);
    }

    /**
     * @test
     */
    public function post_detail_shows_complete_content()
    {
        $post = Post::factory()->create([
            'title' => 'Complete Post',
            'content' => 'Full content here with details',
            'status' => true,
            'user_id' => $this->admin->id,
        ]);

        $response = $this->get(route('posts.show', $post->id));

        $response->assertSee('Complete Post');
        $response->assertSee('Full content here with details');
    }

    /**
     * @test
     */
    public function pagination_second_page_works()
    {
        Post::factory()->count(15)->create([
            'status' => true,
            'user_id' => $this->admin->id,
        ]);

        $response = $this->get(route('posts.index', ['page' => 2]));

        $response->assertOk();
        $response->assertViewHas('posts');
    }

    /**
     * @test
     */
    public function search_with_special_characters_works()
    {
        Post::factory()->create([
            'title' => 'Post with & special * characters',
            'status' => true,
            'user_id' => $this->admin->id,
        ]);

        $response = $this->get(route('posts.index', ['search' => '& special']));

        $response->assertOk();
    }

    /**
     * @test
     */
    public function multiple_authors_posts_display_correctly()
    {
        $author1 = User::factory()->create(['role' => 'admin', 'name' => 'Author One']);
        $author2 = User::factory()->create(['role' => 'admin', 'name' => 'Author Two']);

        Post::factory()->create([
            'status' => true,
            'user_id' => $author1->id,
        ]);

        Post::factory()->create([
            'status' => true,
            'user_id' => $author2->id,
        ]);

        $response = $this->get(route('posts.index'));

        $response->assertSee('Author One');
        $response->assertSee('Author Two');
    }
}
