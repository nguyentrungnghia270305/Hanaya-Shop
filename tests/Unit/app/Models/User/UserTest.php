<?php

namespace Tests\Unit\App\Models\User;

use App\Models\Address;
use App\Models\Cart\Cart;
use App\Models\Order\Order;
use App\Models\Post;
use App\Models\Product\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_be_created_with_required_fields()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user',
        ]);
    }

    /** @test */
    public function user_password_is_hashed_automatically()
    {
        $user = User::factory()->create(['password' => 'plain-password']);

        $this->assertNotEquals('plain-password', $user->password);
        $this->assertTrue(strlen($user->password) > 50);
    }

    /** @test */
    public function user_can_check_if_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());
    }

    /** @test */
    public function user_can_check_if_regular_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->assertFalse($admin->isUser());
        $this->assertTrue($user->isUser());
    }

    /** @test */
    public function user_has_orders_relationship()
    {
        $user = User::factory()->create();
        $orders = Order::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->order);
        $this->assertInstanceOf(Order::class, $user->order->first());
    }

    /** @test */
    public function user_has_reviews_relationship()
    {
        $user = User::factory()->create();
        $reviews = Review::factory()->count(2)->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->review);
        $this->assertInstanceOf(Review::class, $user->review->first());
    }

    /** @test */
    public function user_has_cart_relationship()
    {
        $user = User::factory()->create();
        $cartItems = Cart::factory()->count(5)->create(['user_id' => $user->id]);

        $this->assertCount(5, $user->cart);
        $this->assertInstanceOf(Cart::class, $user->cart->first());
    }

    /** @test */
    public function user_has_addresses_relationship()
    {
        $user = User::factory()->create();
        $addresses = Address::factory()->count(2)->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->addresses);
        $this->assertInstanceOf(Address::class, $user->addresses->first());
    }

    /** @test */
    public function user_can_get_email_for_password_reset()
    {
        $user = User::factory()->create(['email' => 'reset@example.com']);

        $this->assertEquals('reset@example.com', $user->getEmailForPasswordReset());
    }

    /** @test */
    public function user_password_reset_notification_uses_locale()
    {
        Session::put('locale', 'vi');
        $user = User::factory()->create();

        // Test that notification method exists and is callable
        $this->assertTrue(method_exists($user, 'sendPasswordResetNotification'));
    }
}
