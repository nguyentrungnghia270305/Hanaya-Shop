<?php

// namespace Tests\Unit\App\Models\Product;

// use App\Models\Order\Order;
// use App\Models\Product\Product;
// use App\Models\Product\Review;
// use App\Models\User;
// use Illuminate\Foundation\Testing\RefreshDatabase;
// use Tests\TestCase;

// class ReviewTest extends TestCase
// {
//     use RefreshDatabase;

//     /** @test */
//     public function review_can_be_created_with_rating_and_comment()
//     {
//         $user = User::factory()->create();
//         $product = Product::factory()->create();
//         $order = Order::factory()->create(['user_id' => $user->id]);

//         $review = Review::factory()->create([
//             'user_id' => $user->id,
//             'product_id' => $product->id,
//             'order_id' => $order->id,
//             'rating' => 5,
//             'comment' => 'Excellent product!',
//         ]);

//         $this->assertDatabaseHas('reviews', [
//             'rating' => 5,
//             'comment' => 'Excellent product!',
//         ]);
//     }

//     /** @test */
//     public function review_belongs_to_product()
//     {
//         $product = Product::factory()->create(['name' => 'Test Product']);
//         $review = Review::factory()->create(['product_id' => $product->id]);

//         $this->assertInstanceOf(Product::class, $review->product);
//         $this->assertEquals('Test Product', $review->product->name);
//     }

//     /** @test */
//     public function review_belongs_to_user()
//     {
//         $user = User::factory()->create(['name' => 'Test User']);
//         $review = Review::factory()->create(['user_id' => $user->id]);

//         $this->assertInstanceOf(User::class, $review->user);
//         $this->assertEquals('Test User', $review->user->name);
//     }

//     /** @test */
//     public function review_belongs_to_order()
//     {
//         $order = Order::factory()->create();
//         $review = Review::factory()->create(['order_id' => $order->id]);

//         $this->assertInstanceOf(Order::class, $review->order);
//         $this->assertEquals($order->id, $review->order->id);
//     }

//     /** @test */
//     public function review_rating_is_cast_to_integer()
//     {
//         $review = Review::factory()->create(['rating' => '4']);

//         $this->assertIsInt($review->fresh()->rating);
//         $this->assertEquals(4, $review->fresh()->rating);
//     }

//     /** @test */
//     public function review_can_have_image_path()
//     {
//         $review = Review::factory()->create([
//             'image_path' => '/images/reviews/review1.jpg',
//         ]);

//         $this->assertEquals('/images/reviews/review1.jpg', $review->image_path);
//     }

//     /** @test */
//     public function review_timestamps_are_managed_automatically()
//     {
//         $review = Review::factory()->create();

//         $this->assertNotNull($review->created_at);
//         $this->assertNotNull($review->updated_at);
//     }

//     /** @test */
//     public function product_can_have_multiple_reviews()
//     {
//         $product = Product::factory()->create();
//         Review::factory()->count(3)->create(['product_id' => $product->id]);

//         $this->assertCount(3, $product->reviews);
//     }
// }
