<?php

namespace Tests\Unit\App\Notifications;

use App\Models\Order\Order;
use App\Models\User;
use App\Notifications\CustomerOrderCancelledNotification;
use App\Notifications\CustomerOrderCompletedNotification;
use App\Notifications\CustomerOrderConfirmedNotification;
use App\Notifications\CustomerOrderShippedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CustomerNotificationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customer_order_confirmed_notification_uses_user_locale()
    {
        Session::put('locale', 'vi');
        $order = Order::factory()->create();

        $notification = new CustomerOrderConfirmedNotification($order);

        $this->assertEquals('vi', $notification->locale);
    }

    /** @test */
    public function customer_order_confirmed_notification_falls_back_to_default_locale()
    {
        Session::forget('locale');
        $order = Order::factory()->create();

        $notification = new CustomerOrderConfirmedNotification($order);

        $this->assertEquals(config('app.locale'), $notification->locale);
    }

    /** @test */
    public function customer_order_confirmed_notification_can_override_locale()
    {
        Session::put('locale', 'vi');
        $order = Order::factory()->create();

        $notification = new CustomerOrderConfirmedNotification($order, 'en');

        $this->assertEquals('en', $notification->locale);
    }

    /** @test */
    public function customer_order_confirmed_notification_has_correct_channels()
    {
        $order = Order::factory()->create();
        $user = User::factory()->create();

        $notification = new CustomerOrderConfirmedNotification($order);
        $channels = $notification->via($user);

        $this->assertEquals(['database', 'mail'], $channels);
    }

    /** @test */
    public function customer_order_confirmed_notification_has_correct_mail_content()
    {
        $order = Order::factory()->create();
        $user = User::factory()->create();

        $notification = new CustomerOrderConfirmedNotification($order);
        $mailMessage = $notification->toMail($user);

        $this->assertStringContainsString('/order/', $mailMessage->actionUrl);
        $this->assertStringNotContainsString('/admin/', $mailMessage->actionUrl);
    }

    /** @test */
    public function customer_order_completed_notification_respects_locale()
    {
        $order = Order::factory()->create();

        $notification = new CustomerOrderCompletedNotification($order, 'vi');

        $this->assertEquals('vi', $notification->locale);
    }

    /** @test */
    public function customer_order_cancelled_notification_can_be_created()
    {
        $order = Order::factory()->create();

        $notification = new CustomerOrderCancelledNotification($order);

        $this->assertInstanceOf(CustomerOrderCancelledNotification::class, $notification);
    }

    /** @test */
    public function customer_can_receive_order_notifications()
    {
        Notification::fake();

        $order = Order::factory()->create();
        $user = User::factory()->create();

        $user->notify(new CustomerOrderConfirmedNotification($order));

        Notification::assertSentTo($user, CustomerOrderConfirmedNotification::class);
    }
}
