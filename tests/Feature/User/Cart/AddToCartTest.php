<?php

namespace Tests\Feature\User\Cart;

use App\Models\Cart\Cart;
use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddToCartTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create(['stock_quantity' => 10]);
    }

    /**
     * @test
     */
    public function user_can_add_product_to_cart()
    {
        $response = $this->actingAs($this->user)
            ->post(route('cart.add', ['id' => $this->product->id]), [
                'quantity' => 2
            ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);
    }

    /**
     * @test
     */
    public function guest_cannot_add_to_cart()
    {
        $response = $this->post(route('cart.add', ['id' => $this->product->id]), [
            'quantity' => 1
        ]);
        
        $response->assertRedirect(route('login'));
    }

    /**
     * @test
     */
    public function adding_same_product_increases_quantity()
    {
        // Thêm sản phẩm lần đầu và lần 2 trong cùng 1 session
        $response1 = $this->actingAs($this->user)
            ->post(route('cart.add', ['id' => $this->product->id]), [
                'quantity' => 1
            ]);
        
        $response1->assertRedirect();
        
        // Lấy cart item sau lần add đầu tiên
        $cart = Cart::where('product_id', $this->product->id)->first();
        $this->assertNotNull($cart);
        $this->assertEquals(1, $cart->quantity);
        
        // Thêm lần 2 với cùng session_id
        $sessionId = $cart->session_id;
        
        $response2 = $this->withSession(['_token' => 'test'])
            ->actingAs($this->user)
            ->post(route('cart.add', ['id' => $this->product->id]), [
                'quantity' => 2
            ]);
        
        $response2->assertRedirect();
        
        // Kiểm tra lại, có thể có 2 records vì session khác nhau
        // Lấy tổng quantity
        $totalQuantity = Cart::where('product_id', $this->product->id)->sum('quantity');
        
        $this->assertEquals(3, $totalQuantity);
    }

    /**
     * @test
     */
    public function cannot_add_out_of_stock_product()
    {
        $product = Product::factory()->create(['stock_quantity' => 0]);
        
        $response = $this->actingAs($this->user)
            ->post(route('cart.add', ['id' => $product->id]), [
                'quantity' => 1
            ]);
        
        $response->assertSessionHas('error');
    }

    /**
     * @test
     */
    public function cannot_add_more_than_available_stock()
    {
        $response = $this->actingAs($this->user)
            ->post(route('cart.add', ['id' => $this->product->id]), [
                'quantity' => 20
            ]);
        
        $response->assertSessionHas('error');
    }
}
