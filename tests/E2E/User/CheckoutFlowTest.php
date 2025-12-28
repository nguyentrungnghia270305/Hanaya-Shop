<?php

namespace Tests\E2E\User;

use App\Models\Address;
use App\Models\Cart\Cart;
use App\Models\Order\Order;
use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create([
            'price' => 100000,
            'stock_quantity' => 50
        ]);
    }

    /**
     * @test
     */
    public function checkout_requires_valid_address()
    {
        $this->actingAs($this->user);
        
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1
        ]);
        
        $response = $this->post(route('checkout.store'), [
            'payment_method' => 'cod'
        ]);
        
        $response->assertSessionHasErrors('address_id');
    }

    /**
     * @test
     */
    public function checkout_requires_payment_method()
    {
        $this->actingAs($this->user);
        
        $address = Address::factory()->create(['user_id' => $this->user->id]);
        
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1
        ]);
        
        $response = $this->post(route('checkout.store'), [
            'address_id' => $address->id
        ]);
        
        $response->assertSessionHasErrors('payment_method');
    }

    /**
     * @test
     */
    public function guest_cannot_access_checkout()
    {
        $response = $this->get(route('checkout.index'));
        
        $response->assertRedirect(route('login'));
    }
}
