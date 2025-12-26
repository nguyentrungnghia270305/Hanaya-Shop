<?php

namespace Tests\Unit\App\Models\Cart;

use App\Models\Cart\Cart;
use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cart_can_be_created_for_authenticated_user()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'session_id' => null,
        ]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    /** @test */
    public function cart_can_be_created_for_guest_session()
    {
        $product = Product::factory()->create();
        $sessionId = 'guest-session-123';

        $cart = Cart::factory()->create([
            'user_id' => null,
            'product_id' => $product->id,
            'quantity' => 1,
            'session_id' => $sessionId,
        ]);

        $this->assertDatabaseHas('carts', [
            'user_id' => null,
            'session_id' => $sessionId,
            'product_id' => $product->id,
        ]);
    }

    /** @test */
    public function cart_belongs_to_product()
    {
        $product = Product::factory()->create(['name' => 'Test Product']);
        $cart = Cart::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(Product::class, $cart->product);
        $this->assertEquals('Test Product', $cart->product->name);
    }

    /** @test */
    public function cart_belongs_to_user()
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $cart->user);
        $this->assertEquals('Test User', $cart->user->name);
    }

    /** @test */
    public function cart_quantity_can_be_updated()
    {
        $cart = Cart::factory()->create(['quantity' => 1]);

        $cart->update(['quantity' => 5]);

        $this->assertEquals(5, $cart->fresh()->quantity);
    }

    /** @test */
    public function multiple_cart_items_can_exist_for_same_user()
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        Cart::factory()->create(['user_id' => $user->id, 'product_id' => $product1->id]);
        Cart::factory()->create(['user_id' => $user->id, 'product_id' => $product2->id]);

        $this->assertCount(2, $user->cart);
    }

    /** @test */
    public function cart_timestamps_are_managed_automatically()
    {
        $cart = Cart::factory()->create();

        $this->assertNotNull($cart->created_at);
        $this->assertNotNull($cart->updated_at);
    }

    /** @test */
    public function cart_has_fillable_attributes()
    {
        $data = [
            'product_id' => Product::factory()->create()->id,
            'user_id' => User::factory()->create()->id,
            'quantity' => 3,
        ];

        $cart = Cart::create($data);

        $this->assertEquals(3, $cart->quantity);
        $this->assertEquals($data['product_id'], $cart->product_id);
    }
}
