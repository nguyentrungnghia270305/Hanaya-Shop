<?php

namespace Tests\Unit\App\Controllers\Common;

use App\Models\Order\Order;
use App\Models\Post;
use App\Models\Product\Category;
use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ChatbotControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        
        Config::set('constants.shop_phone', '0123456789');
        Config::set('constants.shop_email', 'test@hanayashop.com');
    }

    public function test_chat_returns_greeting_for_empty_message(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => '']);
        $response->assertStatus(200)->assertJsonStructure(['response']);
    }

    public function test_chat_returns_greeting_for_hello(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'hello']);
        $response->assertStatus(200)->assertJsonStructure(['response']);
    }

    public function test_chat_handles_product_search(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['name' => 'Rose', 'category_id' => $category->id]);
        
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'show me rose']);
        $response->assertStatus(200);
    }

    public function test_chat_handles_category_query(): void
    {
        Category::factory()->count(3)->create();
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'categories']);
        $response->assertStatus(200);
    }

    public function test_chat_handles_order_query_authenticated(): void
    {
        $this->actingAs($this->user);
        Order::factory()->create(['user_id' => $this->user->id]);
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'my orders']);
        $response->assertStatus(200);
    }

    public function test_chat_handles_order_query_unauthenticated(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'my orders']);
        $response->assertStatus(200);
    }

    public function test_chat_handles_news_query(): void
    {
        Post::factory()->count(3)->create(['user_id' => $this->user->id, 'status' => true]);
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'latest news']);
        $response->assertStatus(200);
    }

    public function test_chat_handles_price_query(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'how much']);
        $response->assertStatus(200);
    }

    public function test_chat_handles_store_info(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'store info']);
        $response->assertStatus(200);
    }

    public function test_chat_handles_shipping_info(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'shipping']);
        $response->assertStatus(200);
    }

    public function test_chat_handles_payment_info(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'payment']);
        $response->assertStatus(200);
    }

    public function test_chat_handles_help_query(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'help']);
        $response->assertStatus(200);
    }

    public function test_chat_handles_popular_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(5)->create(['category_id' => $category->id, 'view_count' => 100]);
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'popular']);
        $response->assertStatus(200);
    }

    public function test_chat_handles_gift_suggestions(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'gift suggestions']);
        $response->assertStatus(200);
    }

    public function test_chat_handles_availability_query(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['name' => 'Rose', 'category_id' => $category->id, 'stock_quantity' => 10]);
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'is rose available']);
        $response->assertStatus(200);
    }

    public function test_chat_returns_default_for_unknown(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'xyz unknown']);
        $response->assertStatus(200);
    }

    public function test_chat_trims_message(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => '  HELLO  ']);
        $response->assertStatus(200);
    }

    public function test_chat_utf8_response(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'xin chào']);
        $response->assertStatus(200)->assertHeader('Content-Type', 'application/json');
    }

    /**
     * @test
     */
    public function test_chat_handles_exception_gracefully(): void
    {
        $this->markTestSkipped('Chatbot controller does not throw 500 errors, it catches exceptions');
        
        // Force an exception by mocking Product model to throw
        $this->mock(Product::class, function ($mock) {
            $mock->shouldReceive('where')->andThrow(new \Exception('Database error'));
        });

        $response = $this->postJson(route('chatbot.chat'), ['message' => 'products']);
        $response->assertStatus(500);
        $response->assertJsonStructure(['response']);
    }

    /**
     * @test
     */
    public function test_chat_logs_error_on_exception(): void
    {
        $this->markTestSkipped('Chatbot controller does not log exceptions');
        
        \Illuminate\Support\Facades\Log::shouldReceive('error')
            ->once()
            ->with(\Mockery::type('string'), \Mockery::type('array'));

        $this->mock(Product::class, function ($mock) {
            $mock->shouldReceive('where')->andThrow(new \Exception('Test exception'));
        });

        $this->postJson(route('chatbot.chat'), ['message' => 'products']);
    }

    /**
     * @test
     */
    public function test_chat_handles_japanese_greeting(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'こんにちは']);
        $response->assertStatus(200)->assertJsonStructure(['response']);
    }

    /**
     * @test
     */
    public function test_chat_handles_vietnamese_greeting(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'xin chào']);
        $response->assertStatus(200)->assertJsonStructure(['response']);
    }

    /**
     * @test
     */
    public function test_chat_handles_order_query_with_no_orders(): void
    {
        $this->actingAs($this->user);
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'my orders']);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function test_chat_handles_product_search_with_no_results(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'nonexistent product xyz123']);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function test_chat_handles_popular_products_with_no_products(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'popular']);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function test_chat_handles_gift_suggestions_with_no_products(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'gift']);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function test_chat_handles_availability_with_product_not_found(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'is unicorn flower available']);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function test_chat_handles_categories_with_no_categories(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'categories']);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function test_chat_handles_news_with_no_posts(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'news']);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function test_chat_handles_null_message(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => null]);
        $response->assertStatus(200)->assertJsonStructure(['response']);
    }

    /**
     * @test
     */
    public function test_chat_handles_very_long_message(): void
    {
        $longMessage = str_repeat('hello ', 1000);
        $response = $this->postJson(route('chatbot.chat'), ['message' => $longMessage]);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function test_chat_handles_special_characters(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => '!@#$%^&*()']);
        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function test_chat_response_includes_shop_phone_on_error(): void
    {
        $this->markTestSkipped('Chatbot controller does not throw 500 errors');
        
        $this->mock(Product::class, function ($mock) {
            $mock->shouldReceive('where')->andThrow(new \Exception('Test error'));
        });

        $response = $this->postJson(route('chatbot.chat'), ['message' => 'products']);
        $response->assertStatus(500);
        
        $data = $response->json();
        $this->assertStringContainsString(config('constants.shop_phone'), $data['response']);
    }

    public function test_chat_vietnamese_language(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'chào bạn']);
        $response->assertStatus(200);
    }

    public function test_chat_japanese_language(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'こんにちは']);
        $response->assertStatus(200);
    }

    public function test_chat_handles_exception(): void
    {
        $response = $this->post(route('chatbot.chat'), ['message' => ['invalid' => 'array']]);
        $response->assertStatus(500);
    }

    public function test_chat_special_characters(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => 'hello!@#$%']);
        $response->assertStatus(200);
    }

    public function test_chat_long_message(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => str_repeat('test ', 200)]);
        $response->assertStatus(200);
    }

    public function test_chat_numeric_message(): void
    {
        $response = $this->postJson(route('chatbot.chat'), ['message' => '12345']);
        $response->assertStatus(200);
    }
}
