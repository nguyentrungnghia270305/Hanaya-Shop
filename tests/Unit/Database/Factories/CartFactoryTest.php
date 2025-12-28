<?php

namespace Tests\Unit\Database\Factories;

use App\Models\Cart\Cart;
use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartFactoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function factory_creates_cart_with_required_fields()
    {
        $cart = Cart::factory()->create();
        
        $this->assertNotNull($cart->product_id);
        $this->assertNotNull($cart->quantity);
        $this->assertDatabaseHas('carts', [
            'id' => $cart->id
        ]);
    }

    /**
     * @test
     */
    public function factory_sets_quantity()
    {
        $cart = Cart::factory()->create();
        
        $this->assertIsInt($cart->quantity);
        $this->assertGreaterThan(0, $cart->quantity);
    }

    /**
     * @test
     */
    public function factory_creates_product_automatically()
    {
        $cart = Cart::factory()->create();
        
        $this->assertInstanceOf(Product::class, $cart->product);
    }

    /**
     * @test
     */
    public function factory_can_create_cart_with_user()
    {
        $cart = Cart::factory()->create(['user_id' => User::factory()->create()->id]);
        
        $this->assertNotNull($cart->user_id);
        $this->assertInstanceOf(User::class, $cart->user);
    }

    /**
     * @test
     */
    public function factory_can_override_attributes()
    {
        $product = Product::factory()->create();
        
        $cart = Cart::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10
        ]);
        
        $this->assertEquals($product->id, $cart->product_id);
        $this->assertEquals(10, $cart->quantity);
    }

    /**
     * @test
     */
    public function factory_can_create_multiple_carts()
    {
        $carts = Cart::factory()->count(5)->create();
        
        $this->assertCount(5, $carts);
        $this->assertEquals(5, Cart::count());
    }
}
