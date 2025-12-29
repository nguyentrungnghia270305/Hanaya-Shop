<?php

namespace Tests\Unit\App\Notifications;

use App\Models\Order\Order;
use App\Models\User;
use App\Notifications\OrderCancelledNotification;
use App\Notifications\OrderCompletedNotification;
use App\Notifications\OrderConfirmedNotification;
use App\Notifications\OrderPaidNotification;
use App\Notifications\OrderShippedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderNotificationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function order_confirmed_notification_uses_english_locale_for_admin()
    {
        $order = Order::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $notification = new OrderConfirmedNotification($order, 'vi');

        $this->assertEquals('en', $notification->locale);
    }

    /** @test */
    public function order_confirmed_notification_has_correct_channels()
    {
        $order = Order::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $notification = new OrderConfirmedNotification($order);
        $channels = $notification->via($admin);

        $this->assertEquals(['database', 'mail'], $channels);
    }

    /** @test */
    public function order_confirmed_notification_has_correct_mail_content()
    {
        $order = Order::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $notification = new OrderConfirmedNotification($order);
        $mailMessage = $notification->toMail($admin);

        $this->assertStringContainsString('admin/order', $mailMessage->actionUrl);
    }

    /** @test */
    public function order_shipped_notification_can_be_created()
    {
        $order = Order::factory()->create();

        $notification = new OrderShippedNotification($order);

        $this->assertInstanceOf(OrderShippedNotification::class, $notification);
        $this->assertEquals($order->id, $notification->order->id);
    }

    /** @test */
    public function order_paid_notification_can_be_created()
    {
        $order = Order::factory()->create();

        $notification = new OrderPaidNotification($order);

        $this->assertInstanceOf(OrderPaidNotification::class, $notification);
    }

    /** @test */
    public function order_completed_notification_can_be_created()
    {
        $order = Order::factory()->create();

        $notification = new OrderCompletedNotification($order);

        $this->assertInstanceOf(OrderCompletedNotification::class, $notification);
    }

    /** @test */
    public function order_cancelled_notification_can_be_created()
    {
        $order = Order::factory()->create();

        $notification = new OrderCancelledNotification($order);

        $this->assertInstanceOf(OrderCancelledNotification::class, $notification);
    }

    /** @test */
    public function admin_can_receive_order_notifications()
    {
        Notification::fake();

        $order = Order::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $admin->notify(new OrderConfirmedNotification($order));

        Notification::assertSentTo($admin, OrderConfirmedNotification::class);
    }
}
