<?php

namespace Tests\Integration\Email;

use App\Models\Order\Order;
use App\Models\Product\Product;
use App\Models\User;
use App\Notifications\CustomerNewOrderPending;
use App\Notifications\CustomerOrderConfirmedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderEmailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function new_order_notification_is_sent_to_customer()
    {
        Notification::fake();
        
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        
        $user->notify(new CustomerNewOrderPending($order));
        
        Notification::assertSentTo($user, CustomerNewOrderPending::class);
    }

    /**
     * @test
     */
    public function order_confirmed_notification_is_sent()
    {
        Notification::fake();
        
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        
        $user->notify(new CustomerOrderConfirmedNotification($order));
        
        Notification::assertSentTo($user, CustomerOrderConfirmedNotification::class);
    }

    /**
     * @test
     */
    public function order_notification_contains_correct_data()
    {
        Notification::fake();
        
        $user = User::factory()->create(['email' => 'customer@test.com']);
        $product = Product::factory()->create(['name' => 'Test Product', 'price' => 100]);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total_amount' => 100
        ]);
        
        $user->notify(new CustomerNewOrderPending($order));
        
        Notification::assertSentTo($user, CustomerNewOrderPending::class, function ($notification, $channels) use ($order) {
            return $notification->order->id === $order->id;
        });
    }

    /**
     * @test
     */
    public function multiple_order_notifications_can_be_sent()
    {
        Notification::fake();
        
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $order1 = Order::factory()->create(['user_id' => $user1->id]);
        $order2 = Order::factory()->create(['user_id' => $user2->id]);
        
        $user1->notify(new CustomerNewOrderPending($order1));
        $user2->notify(new CustomerNewOrderPending($order2));
        
        Notification::assertSentTo($user1, CustomerNewOrderPending::class);
        Notification::assertSentTo($user2, CustomerNewOrderPending::class);
        Notification::assertCount(2);
    }
}
