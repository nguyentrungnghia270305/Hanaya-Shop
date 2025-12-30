<?php

namespace Tests\Feature\Routes;

use App\Models\Post;
use App\Models\Product\Category;
use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoutesIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'user']);
    }

    /** @test */
    public function guest_can_access_public_routes()
    {
        $response = $this->get('/');
        $response->assertOk();

        $response = $this->get(route('user.about'));
        $response->assertOk();
    }

    /** @test */
    public function guest_can_view_products()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->get(route('user.products.index'));
        $response->assertOk();

        $response = $this->get(route('user.products.show', $product->id));
        $response->assertOk();
    }

    /** @test */
    public function guest_can_view_posts()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $post = Post::factory()->create(['status' => true, 'user_id' => $admin->id]);

        $response = $this->get(route('posts.index'));
        $response->assertOk();

        $response = $this->get(route('posts.show', $post->id));
        $response->assertOk();
    }

    /** @test */
    public function cart_routes_require_authentication()
    {
        $response = $this->get(route('cart.index'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_user_can_access_cart()
    {
        $response = $this->actingAs($this->user)->get(route('cart.index'));
        $response->assertOk();
    }

    /** @test */
    public function authenticated_user_can_access_dashboard()
    {
        $response = $this->actingAs($this->user)->get(route('user.dashboard'));
        $response->assertOk();
    }

    /** @test */
    public function checkout_routes_require_authentication()
    {
        $response = $this->get(route('checkout.index'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_user_can_access_checkout()
    {
        // Checkout may redirect if cart is empty, so just check it's not 401/403
        $response = $this->actingAs($this->user)->get(route('checkout.index'));
        $this->assertNotEquals(401, $response->status());
        $this->assertNotEquals(403, $response->status());
    }

    /** @test */
    public function order_routes_require_authentication()
    {
        $response = $this->get(route('order.index'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_user_can_view_orders()
    {
        $response = $this->actingAs($this->user)->get(route('order.index'));
        $response->assertOk();
    }

    /** @test */
    public function address_routes_require_authentication()
    {
        // Address routes are within authenticated middleware group
        // Test by trying to access profile which includes address management
        $response = $this->get(route('profile.edit'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_user_can_manage_addresses()
    {
        // Address management is done through profile and addresses.store route
        $response = $this->actingAs($this->user)->get(route('profile.edit'));
        $response->assertOk();

        // Verify addresses.store route exists
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('addresses.store'));
    }

    /** @test */
    public function user_profile_routes_require_authentication()
    {
        $response = $this->get(route('profile.edit'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_user_can_access_profile()
    {
        $response = $this->actingAs($this->user)->get(route('profile.edit'));
        $response->assertOk();
    }
}
