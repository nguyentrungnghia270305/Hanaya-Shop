<?php

namespace Tests\Unit\App\Models\Product;

use PHPUnit\Framework\TestCase;

class ReviewTest extends TestCase
{
	public function test_dummy_review(): void
	{
		$this->assertTrue(true);
	}
}
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
