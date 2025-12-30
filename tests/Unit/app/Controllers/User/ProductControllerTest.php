<?php

namespace Tests\Unit\App\Controllers\User;

use App\Models\Order\Order;
use App\Models\Order\OrderDetail;
use App\Models\Product\Category;
use App\Models\Product\Product;
use App\Models\Product\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\ControllerTestCase;

class ProductControllerTest extends ControllerTestCase
{
    use RefreshDatabase;

    /**
     * Test index displays paginated products
     */
    public function test_index_displays_paginated_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(15)->create(['category_id' => $category->id]);

        $response = $this->get(route('product.index'));

        $response->assertStatus(200);
        $response->assertViewIs('page.products.index');
        $response->assertViewHas('products');
        $products = $response->viewData('products');
        $this->assertEquals(10, $products->count());
    }

    /**
     * Test index eager loads category relationship
     */
    public function test_index_eager_loads_category(): void
    {
        $category = Category::factory()->create(['name' => 'Test Category']);
        Product::factory()->create(['category_id' => $category->id]);

        $response = $this->get(route('product.index'));

        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertTrue($products->first()->relationLoaded('category'));
        $this->assertEquals('Test Category', $products->first()->category->name);
    }

    /**
     * Test index filters by category ID
     */
    public function test_index_filters_by_category_id(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        Product::factory()->count(5)->create(['category_id' => $category1->id]);
        Product::factory()->count(3)->create(['category_id' => $category2->id]);

        $response = $this->get(route('product.index', ['category' => $category1->id]));

        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertEquals(5, $products->total());
        $this->assertEquals($category1->id, $products->first()->category_id);
    }

    /**
     * Test index filters by category name
     */
    public function test_index_filters_by_category_name(): void
    {
        $category = Category::factory()->create(['name' => 'Soap Flower']);
        Product::factory()->count(5)->create(['category_id' => $category->id]);
        Category::factory()->create(['name' => 'Fresh Flower']);

        $response = $this->get(route('product.index', ['category_name' => 'soap-flower']));

        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertEquals(5, $products->total());
    }

    /**
     * Test index searches by keyword in product name
     */
    public function test_index_searches_by_keyword_in_name(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['name' => 'Red Rose', 'category_id' => $category->id]);
        Product::factory()->create(['name' => 'Blue Tulip', 'category_id' => $category->id]);

        $response = $this->get(route('product.index', ['q' => 'Rose']));

        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertEquals(1, $products->total());
        $this->assertStringContainsString('Rose', $products->first()->name);
    }

    /**
     * Test index searches by keyword in descriptions
     */
    public function test_index_searches_by_keyword_in_descriptions(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create([
            'name' => 'Product 1',
            'descriptions' => 'Beautiful red roses',
            'category_id' => $category->id,
        ]);
        Product::factory()->create([
            'name' => 'Product 2',
            'descriptions' => 'Yellow sunflowers',
            'category_id' => $category->id,
        ]);

        $response = $this->get(route('product.index', ['q' => 'roses']));

        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertEquals(1, $products->total());
    }

    /**
     * Test index searches by category name
     */
    public function test_index_searches_by_category_name(): void
    {
        $category1 = Category::factory()->create(['name' => 'Wedding Flowers']);
        $category2 = Category::factory()->create(['name' => 'Birthday Gifts']);
        Product::factory()->create(['category_id' => $category1->id]);
        Product::factory()->create(['category_id' => $category2->id]);

        $response = $this->get(route('product.index', ['q' => 'Wedding']));

        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertEquals(1, $products->total());
    }

    /**
     * Test index sorts by price ascending
     */
    public function test_index_sorts_by_price_ascending(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['price' => 50000, 'category_id' => $category->id]);
        Product::factory()->create(['price' => 30000, 'category_id' => $category->id]);
        Product::factory()->create(['price' => 40000, 'category_id' => $category->id]);

        $response = $this->get(route('product.index', ['sort' => 'asc']));

        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertEquals(30000, $products->first()->price);
    }

    /**
     * Test index sorts by price descending
     */
    public function test_index_sorts_by_price_descending(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['price' => 30000, 'category_id' => $category->id]);
        Product::factory()->create(['price' => 50000, 'category_id' => $category->id]);
        Product::factory()->create(['price' => 40000, 'category_id' => $category->id]);

        $response = $this->get(route('product.index', ['sort' => 'desc']));

        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertEquals(50000, $products->first()->price);
    }

    /**
     * Test index sorts by discount (sale)
     */
    public function test_index_sorts_by_discount(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['discount_percent' => 10, 'category_id' => $category->id]);
        Product::factory()->create(['discount_percent' => 30, 'category_id' => $category->id]);
        Product::factory()->create(['discount_percent' => 20, 'category_id' => $category->id]);

        $response = $this->get(route('product.index', ['sort' => 'sale']));

        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertEquals(30, $products->first()->discount_percent);
    }

    /**
     * Test index sorts by view count
     */
    public function test_index_sorts_by_view_count(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['view_count' => 100, 'category_id' => $category->id]);
        Product::factory()->create(['view_count' => 500, 'category_id' => $category->id]);
        Product::factory()->create(['view_count' => 200, 'category_id' => $category->id]);

        $response = $this->get(route('product.index', ['sort' => 'views']));

        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertEquals(500, $products->first()->view_count);
    }

    /**
     * Test index sorts by bestseller
     */
    public function test_index_sorts_by_bestseller(): void
    {
        $category = Category::factory()->create();
        $product1 = Product::factory()->create(['category_id' => $category->id]);
        $product2 = Product::factory()->create(['category_id' => $category->id]);

        $user = User::factory()->create();
        $order1 = Order::factory()->create(['user_id' => $user->id]);
        $order2 = Order::factory()->create(['user_id' => $user->id]);

        OrderDetail::create([
            'order_id' => $order1->id,
            'product_id' => $product1->id,
            'quantity' => 10,
            'price' => 100000,
        ]);
        OrderDetail::create([
            'order_id' => $order2->id,
            'product_id' => $product2->id,
            'quantity' => 5,
            'price' => 100000,
        ]);

        $response = $this->get(route('product.index', ['sort' => 'bestseller']));

        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertEquals($product1->id, $products->first()->id);
    }

    /**
     * Test index sorts by latest (default)
     */
    public function test_index_sorts_by_latest_default(): void
    {
        $category = Category::factory()->create();
        $old = Product::factory()->create(['category_id' => $category->id, 'created_at' => now()->subDays(5)]);
        $new = Product::factory()->create(['category_id' => $category->id, 'created_at' => now()]);

        $response = $this->get(route('product.index', ['sort' => 'latest']));

        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertEquals($new->id, $products->first()->id);
    }

    /**
     * Test index passes categories to view
     */
    public function test_index_passes_categories_to_view(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->get(route('product.index'));

        $response->assertStatus(200);
        $response->assertViewHas('categories');
        $this->assertCount(3, $response->viewData('categories'));
    }

    /**
     * Test index maintains query parameters in pagination
     */
    public function test_index_maintains_query_parameters_in_pagination(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(15)->create(['category_id' => $category->id, 'price' => 100000]);

        $response = $this->get(route('product.index', ['sort' => 'asc', 'q' => 'test']));

        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertStringContainsString('sort=asc', $products->url(2));
        $this->assertStringContainsString('q=test', $products->url(2));
    }

    /**
     * Test show displays product details
     */
    public function test_show_displays_product_details(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->get(route('product.show', $product->id));

        $response->assertStatus(200);
        $response->assertViewIs('page.products.productDetail');
        $response->assertViewHas('product');
        $this->assertEquals($product->id, $response->viewData('product')->id);
    }

    /**
     * Test show increments view count
     */
    public function test_show_increments_view_count(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'view_count' => 10]);

        $this->get(route('product.show', $product->id));

        $product->refresh();
        $this->assertEquals(11, $product->view_count);
    }

    /**
     * Test show displays paginated reviews
     */
    // public function test_show_displays_paginated_reviews(): void
    // {
    //     $category = Category::factory()->create();
    //     $product = Product::factory()->create(['category_id' => $category->id]);
    //     $user = User::factory()->create();
    //     Review::factory()->count(10)->create(['product_id' => $product->id, 'user_id' => $user->id]);

    //     $response = $this->get(route('product.show', $product->id));

    //     $response->assertStatus(200);
    //     $response->assertViewHas('reviews');
    //     $reviews = $response->viewData('reviews');
    //     $this->assertEquals(5, $reviews->count());
    // }

    // /**
    //  * Test show eager loads user relationship for reviews
    //  */
    // public function test_show_eager_loads_user_relationship_for_reviews(): void
    // {
    //     $category = Category::factory()->create();
    //     $product = Product::factory()->create(['category_id' => $category->id]);
    //     $user = User::factory()->create(['name' => 'Test User']);
    //     Review::factory()->create(['product_id' => $product->id, 'user_id' => $user->id]);

    //     $response = $this->get(route('product.show', $product->id));

    //     $response->assertStatus(200);
    //     $reviews = $response->viewData('reviews');
    //     $this->assertTrue($reviews->first()->relationLoaded('user'));
    //     $this->assertEquals('Test User', $reviews->first()->user->name);
    // }

    // /**
    //  * Test show orders reviews by creation date desc
    //  */
    // public function test_show_orders_reviews_by_creation_date_desc(): void
    // {
    //     $category = Category::factory()->create();
    //     $product = Product::factory()->create(['category_id' => $category->id]);
    //     $user = User::factory()->create();
    //     $old = Review::factory()->create([
    //         'product_id' => $product->id,
    //         'user_id' => $user->id,
    //         'created_at' => now()->subDays(5)
    //     ]);
    //     $new = Review::factory()->create([
    //         'product_id' => $product->id,
    //         'user_id' => $user->id,
    //         'created_at' => now()
    //     ]);

    //     $response = $this->get(route('product.show', $product->id));

    //     $reviews = $response->viewData('reviews');
    //     $this->assertEquals($new->id, $reviews->first()->id);
    // }

    // /**
    //  * Test show calculates average rating
    //  */
    // public function test_show_calculates_average_rating(): void
    // {
    //     $category = Category::factory()->create();
    //     $product = Product::factory()->create(['category_id' => $category->id]);
    //     $user = User::factory()->create();
    //     Review::factory()->create(['product_id' => $product->id, 'user_id' => $user->id, 'rating' => 5]);
    //     Review::factory()->create(['product_id' => $product->id, 'user_id' => $user->id, 'rating' => 3]);

    //     $response = $this->get(route('product.show', $product->id));

    //     $response->assertViewHas('averageRating');
    //     $this->assertEquals(4, $response->viewData('averageRating'));
    // }

    /**
     * Test show defaults to 5 stars when no reviews
     */
    public function test_show_defaults_to_five_stars_when_no_reviews(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->get(route('product.show', $product->id));

        $response->assertViewHas('averageRating');
        $this->assertEquals(5, $response->viewData('averageRating'));
    }

    /**
     * Test show provides total review count
     */
    // public function test_show_provides_total_review_count(): void
    // {
    //     $category = Category::factory()->create();
    //     $product = Product::factory()->create(['category_id' => $category->id]);
    //     $user = User::factory()->create();
    //     Review::factory()->count(7)->create(['product_id' => $product->id, 'user_id' => $user->id]);

    //     $response = $this->get(route('product.show', $product->id));

    //     $response->assertViewHas('totalReviews');
    //     $this->assertEquals(7, $response->viewData('totalReviews'));
    // }

    /**
     * Test show displays related products from same category
     */
    public function test_show_displays_related_products_from_same_category(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category1->id]);
        Product::factory()->count(5)->create(['category_id' => $category1->id]);
        Product::factory()->count(3)->create(['category_id' => $category2->id]);

        $response = $this->get(route('product.show', $product->id));

        $response->assertViewHas('relatedProducts');
        $related = $response->viewData('relatedProducts');
        $this->assertEquals(5, $related->count());
    }

    /**
     * Test show excludes current product from related products
     */
    public function test_show_excludes_current_product_from_related_products(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $response = $this->get(route('product.show', $product->id));

        $related = $response->viewData('relatedProducts');
        $relatedIds = $related->pluck('id')->toArray();
        $this->assertNotContains($product->id, $relatedIds);
    }

    /**
     * Test show limits related products to 8
     */
    public function test_show_limits_related_products_to_eight(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        Product::factory()->count(15)->create(['category_id' => $category->id]);

        $response = $this->get(route('product.show', $product->id));

        $related = $response->viewData('relatedProducts');
        $this->assertLessThanOrEqual(8, $related->count());
    }

    /**
     * Test show returns 404 for non-existent product
     */
    public function test_show_returns_404_for_non_existent_product(): void
    {
        $response = $this->get(route('product.show', 9999));

        $response->assertStatus(404);
    }

    /**
     * Test show uses caching for product details
     */
    public function test_show_uses_caching_for_product_details(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->get(route('product.show', $product->id));

        $this->assertNotNull(Cache::get("product_detail_{$product->id}"));
    }
}
