<?php

namespace Tests\Unit\Notifications;

use App\Models\Order\Order;
use App\Models\User;
use App\Notifications\CustomerOrderCompletedNotification;
use App\Notifications\OrderCompletedNotification;
use App\Notifications\OrderPaidNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Tests\TestCase;

class NotificationClassesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $customer;

    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin User']);
        $this->customer = User::factory()->create(['role' => 'user', 'name' => 'Customer User']);
        $this->order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'total_price' => 150000,
        ]);
    }

    // ===== OrderPaidNotification Tests =====

    public function test_order_paid_notification_uses_mail_and_database_channels(): void
    {
        $notification = new OrderPaidNotification($this->order);

        $channels = $notification->via($this->admin);

        $this->assertContains('mail', $channels);
        $this->assertContains('database', $channels);
    }

    public function test_order_paid_notification_always_uses_english_locale(): void
    {
        $notification = new OrderPaidNotification($this->order, 'vi');

        $this->assertEquals('en', $notification->locale);
    }

    public function test_order_paid_notification_creates_mail_message(): void
    {
        $notification = new OrderPaidNotification($this->order);

        $mailMessage = $notification->toMail($this->admin);

        $this->assertNotNull($mailMessage);
        $this->assertStringContainsString($this->admin->name, $mailMessage->render());
    }

    public function test_order_paid_notification_includes_order_id_in_array(): void
    {
        $notification = new OrderPaidNotification($this->order);

        $array = $notification->toArray($this->admin);

        $this->assertArrayHasKey('order_id', $array);
        $this->assertEquals($this->order->id, $array['order_id']);
    }

    public function test_order_paid_notification_includes_amount_in_array(): void
    {
        $notification = new OrderPaidNotification($this->order);

        $array = $notification->toArray($this->admin);

        $this->assertArrayHasKey('amount', $array);
        $this->assertEquals(150000, $array['amount']);
    }

    public function test_order_paid_notification_includes_message_in_array(): void
    {
        $notification = new OrderPaidNotification($this->order);

        $array = $notification->toArray($this->admin);

        $this->assertArrayHasKey('message', $array);
        $this->assertNotEmpty($array['message']);
    }

    public function test_order_paid_notification_mail_has_action_button(): void
    {
        $notification = new OrderPaidNotification($this->order);

        $mailMessage = $notification->toMail($this->admin);

        $this->assertNotNull($mailMessage->actionUrl);
        $this->assertStringContainsString('/admin/order/', $mailMessage->actionUrl);
    }

    // ===== OrderCompletedNotification Tests =====

    public function test_order_completed_notification_uses_mail_and_database_channels(): void
    {
        $notification = new OrderCompletedNotification($this->order);

        $channels = $notification->via($this->admin);

        $this->assertContains('mail', $channels);
        $this->assertContains('database', $channels);
    }

    public function test_order_completed_notification_always_uses_english_locale(): void
    {
        $notification = new OrderCompletedNotification($this->order, 'ja');

        $this->assertEquals('en', $notification->locale);
    }

    public function test_order_completed_notification_creates_mail_message(): void
    {
        $notification = new OrderCompletedNotification($this->order);

        $mailMessage = $notification->toMail($this->admin);

        $this->assertNotNull($mailMessage);
        $this->assertStringContainsString($this->admin->name, $mailMessage->render());
    }

    public function test_order_completed_notification_includes_order_id_in_array(): void
    {
        $notification = new OrderCompletedNotification($this->order);

        $array = $notification->toArray($this->admin);

        $this->assertArrayHasKey('order_id', $array);
        $this->assertEquals($this->order->id, $array['order_id']);
    }

    public function test_order_completed_notification_includes_message_in_array(): void
    {
        $notification = new OrderCompletedNotification($this->order);

        $array = $notification->toArray($this->admin);

        $this->assertArrayHasKey('message', $array);
        $this->assertNotEmpty($array['message']);
    }

    public function test_order_completed_notification_mail_has_action_button(): void
    {
        $notification = new OrderCompletedNotification($this->order);

        $mailMessage = $notification->toMail($this->admin);

        $this->assertNotNull($mailMessage->actionUrl);
        $this->assertStringContainsString('/admin/order/', $mailMessage->actionUrl);
    }

    // ===== CustomerOrderCompletedNotification Tests =====

    public function test_customer_order_completed_notification_uses_mail_and_database_channels(): void
    {
        $notification = new CustomerOrderCompletedNotification($this->order);

        $channels = $notification->via($this->customer);

        $this->assertContains('mail', $channels);
        $this->assertContains('database', $channels);
    }

    public function test_customer_order_completed_notification_respects_provided_locale(): void
    {
        $notification = new CustomerOrderCompletedNotification($this->order, 'vi');

        $this->assertEquals('vi', $notification->locale);
    }

    public function test_customer_order_completed_notification_uses_default_locale_when_null(): void
    {
        $notification = new CustomerOrderCompletedNotification($this->order, null);

        $this->assertEquals(config('app.locale'), $notification->locale);
    }

    public function test_customer_order_completed_notification_creates_mail_message(): void
    {
        $notification = new CustomerOrderCompletedNotification($this->order);

        $mailMessage = $notification->toMail($this->customer);

        $this->assertNotNull($mailMessage);
        $this->assertStringContainsString($this->customer->name, $mailMessage->render());
    }

    public function test_customer_order_completed_notification_includes_order_id_in_array(): void
    {
        $notification = new CustomerOrderCompletedNotification($this->order);

        $array = $notification->toArray($this->customer);

        $this->assertArrayHasKey('order_id', $array);
        $this->assertEquals($this->order->id, $array['order_id']);
    }

    public function test_customer_order_completed_notification_includes_message_in_array(): void
    {
        $notification = new CustomerOrderCompletedNotification($this->order);

        $array = $notification->toArray($this->customer);

        $this->assertArrayHasKey('message', $array);
        $this->assertNotEmpty($array['message']);
    }

    public function test_customer_order_completed_notification_mail_has_customer_action_url(): void
    {
        $notification = new CustomerOrderCompletedNotification($this->order);

        $mailMessage = $notification->toMail($this->customer);

        $this->assertNotNull($mailMessage->actionUrl);
        $this->assertStringContainsString('/order/', $mailMessage->actionUrl);
        $this->assertStringNotContainsString('/admin/', $mailMessage->actionUrl);
    }

    public function test_customer_order_completed_notification_supports_japanese_locale(): void
    {
        $notification = new CustomerOrderCompletedNotification($this->order, 'ja');

        $this->assertEquals('ja', $notification->locale);
    }

    public function test_notifications_can_be_sent_to_users(): void
    {
        NotificationFacade::fake();

        $adminNotification = new OrderPaidNotification($this->order);
        $this->admin->notify($adminNotification);

        NotificationFacade::assertSentTo(
            $this->admin,
            OrderPaidNotification::class
        );

        $customerNotification = new CustomerOrderCompletedNotification($this->order);
        $this->customer->notify($customerNotification);

        NotificationFacade::assertSentTo(
            $this->customer,
            CustomerOrderCompletedNotification::class
        );
    }
}
