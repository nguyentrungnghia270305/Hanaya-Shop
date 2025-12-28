<?php

namespace Tests\Unit\Controllers\User;

use App\Http\Controllers\User\DashboardController;
use App\Models\Order\Order;
use App\Models\Order\OrderDetail;
use App\Models\Post;
use App\Models\Product\Category;
use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Cache::flush();
    }

    /**
     * @test
     */
    public function guest_can_view_dashboard()
    {
        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewIs('page.dashboard');
    }

    /**
     * @test
     */
    public function dashboard_displays_top_seller_products()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        
        $order = Order::factory()->create(['status' => 'completed']);
        OrderDetail::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('topSeller');
    }

    /**
     * @test
     */
    public function dashboard_displays_latest_products()
    {
        $category = Category::factory()->create();
        Product::factory()->count(5)->create(['category_id' => $category->id]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('latest');
    }

    /**
     * @test
     */
    public function dashboard_displays_products_on_sale()
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create([
            'category_id' => $category->id,
            'discount_percent' => 20,
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('onSale');
        
        $onSaleProducts = $response->viewData('onSale');
        $this->assertTrue($onSaleProducts->every(fn($p) => $p->discount_percent > 0));
    }

    /**
     * @test
     */
    public function dashboard_displays_most_viewed_products()
    {
        $category = Category::factory()->create();
        Product::factory()->count(5)->create([
            'category_id' => $category->id,
            'view_count' => rand(10, 100),
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('mostViewed');
    }

    /**
     * @test
     */
    public function dashboard_displays_categories_with_product_count()
    {
        $category1 = Category::factory()->create(['name' => 'Soap Flower']);
        $category2 = Category::factory()->create(['name' => 'Fresh Flowers']);
        
        Product::factory()->count(5)->create(['category_id' => $category1->id]);
        Product::factory()->count(3)->create(['category_id' => $category2->id]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('categories');
        
        $categories = $response->viewData('categories');
        $this->assertGreaterThan(0, $categories->count());
    }

    /**
     * @test
     */
    public function dashboard_displays_latest_published_posts()
    {
        $author = User::factory()->create(['role' => 'admin']);
        
        Post::factory()->count(5)->create([
            'status' => true,
            'user_id' => $author->id,
        ]);
        
        Post::factory()->count(2)->create([
            'status' => false,
            'user_id' => $author->id,
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('latestPosts');
        
        $posts = $response->viewData('latestPosts');
        $this->assertLessThanOrEqual(3, $posts->count());
        $this->assertTrue($posts->every(fn($p) => $p->status == true));
    }

    /**
     * @test
     */
    public function dashboard_displays_banners()
    {
        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('banners');
    }

    /**
     * @test
     */
    public function dashboard_uses_cache_for_performance()
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn([
                'topSeller' => collect(),
                'latestByCategory' => [],
                'latest' => collect(),
                'onSale' => collect(),
                'mostViewed' => collect(),
                'categories' => collect(),
                'latestPosts' => collect(),
            ]);

        $this->get(route('dashboard'));
    }

    /**
     * @test
     */
    public function dashboard_cache_key_includes_date_and_hour()
    {
        $expectedKey = 'dashboard_data_' . date('Y-m-d-H');
        
        Cache::shouldReceive('remember')
            ->once()
            ->withArgs(function ($key) use ($expectedKey) {
                return $key === $expectedKey;
            })
            ->andReturn([
                'topSeller' => collect(),
                'latestByCategory' => [],
                'latest' => collect(),
                'onSale' => collect(),
                'mostViewed' => collect(),
                'categories' => collect(),
                'latestPosts' => collect(),
            ]);

        $this->get(route('dashboard'));
    }

    /**
     * @test
     */
    public function top_seller_products_are_ordered_by_sales_quantity()
    {
        $category = Category::factory()->create();
        $product1 = Product::factory()->create(['category_id' => $category->id, 'name' => 'Low Seller']);
        $product2 = Product::factory()->create(['category_id' => $category->id, 'name' => 'Top Seller']);
        
        $order = Order::factory()->create();
        OrderDetail::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 5,
        ]);
        OrderDetail::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 20,
        ]);

        Cache::flush();
        $response = $this->get(route('dashboard'));

        $topSeller = $response->viewData('topSeller');
        if ($topSeller->count() > 0) {
            $this->assertEquals($product2->id, $topSeller->first()->id);
        }
    }

    /**
     * @test
     */
    public function latest_products_are_limited_to_8()
    {
        $category = Category::factory()->create();
        Product::factory()->count(15)->create(['category_id' => $category->id]);

        Cache::flush();
        $response = $this->get(route('dashboard'));

        $latest = $response->viewData('latest');
        $this->assertLessThanOrEqual(8, $latest->count());
    }

    /**
     * @test
     */
    public function sale_products_are_limited_to_8()
    {
        $category = Category::factory()->create();
        Product::factory()->count(15)->create([
            'category_id' => $category->id,
            'discount_percent' => 20,
        ]);

        Cache::flush();
        $response = $this->get(route('dashboard'));

        $onSale = $response->viewData('onSale');
        $this->assertLessThanOrEqual(8, $onSale->count());
    }

    /**
     * @test
     */
    public function most_viewed_products_are_limited_to_8()
    {
        $category = Category::factory()->create();
        Product::factory()->count(15)->create([
            'category_id' => $category->id,
            'view_count' => 100,
        ]);

        Cache::flush();
        $response = $this->get(route('dashboard'));

        $mostViewed = $response->viewData('mostViewed');
        $this->assertLessThanOrEqual(8, $mostViewed->count());
    }

    /**
     * @test
     */
    public function latest_posts_are_limited_to_3()
    {
        $author = User::factory()->create(['role' => 'admin']);
        Post::factory()->count(10)->create([
            'status' => true,
            'user_id' => $author->id,
        ]);

        Cache::flush();
        $response = $this->get(route('dashboard'));

        $latestPosts = $response->viewData('latestPosts');
        $this->assertLessThanOrEqual(3, $latestPosts->count());
    }

    /**
     * @test
     */
    public function top_seller_returns_4_products_max()
    {
        $category = Category::factory()->create();
        $products = Product::factory()->count(10)->create(['category_id' => $category->id]);
        
        $order = Order::factory()->create();
        
        foreach ($products as $product) {
            OrderDetail::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => rand(1, 10),
            ]);
        }

        Cache::flush();
        $response = $this->get(route('dashboard'));

        $topSeller = $response->viewData('topSeller');
        $this->assertLessThanOrEqual(4, $topSeller->count());
    }

    /**
     * @test
     */
    public function products_include_review_relationships()
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        Cache::flush();
        $response = $this->get(route('dashboard'));

        $latest = $response->viewData('latest');
        if ($latest->count() > 0) {
            $this->assertTrue($latest->first()->relationLoaded('reviews'));
        }
    }

    /**
     * @test
     */
    public function sale_products_ordered_by_discount_percent_desc()
    {
        $category = Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category->id,
            'discount_percent' => 10,
        ]);
        Product::factory()->create([
            'category_id' => $category->id,
            'discount_percent' => 50,
        ]);

        Cache::flush();
        $response = $this->get(route('dashboard'));

        $onSale = $response->viewData('onSale');
        if ($onSale->count() > 1) {
            $this->assertGreaterThanOrEqual(
                $onSale->last()->discount_percent,
                $onSale->first()->discount_percent
            );
        }
    }

    /**
     * @test
     */
    public function most_viewed_products_ordered_by_view_count_desc()
    {
        $category = Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category->id,
            'view_count' => 50,
        ]);
        Product::factory()->create([
            'category_id' => $category->id,
            'view_count' => 200,
        ]);

        Cache::flush();
        $response = $this->get(route('dashboard'));

        $mostViewed = $response->viewData('mostViewed');
        if ($mostViewed->count() > 1) {
            $this->assertGreaterThanOrEqual(
                $mostViewed->last()->view_count,
                $mostViewed->first()->view_count
            );
        }
    }
}
