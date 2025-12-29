<?php

namespace Tests\Unit\App\Controllers\Admin;

use App\Http\Controllers\Admin\OrdersController;
use App\Models\Address;
use App\Models\Order\Order;
use App\Models\Order\OrderDetail;
use App\Models\Order\Payment;
use App\Models\Product\Product;
use App\Models\User;
use App\Notifications\CustomerOrderCancelledNotification;
use App\Notifications\CustomerOrderCompletedNotification;
use App\Notifications\CustomerOrderConfirmedNotification;
use App\Notifications\CustomerOrderShippedNotification;
use App\Notifications\OrderCancelledNotification;
use App\Notifications\OrderConfirmedNotification;
use App\Notifications\OrderPaidNotification;
use App\Notifications\OrderShippedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class OrdersControllerTest extends TestCase
{
    use RefreshDatabase;

    protected OrdersController $controller;
    protected User $admin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new OrdersController();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->customer = User::factory()->create(['role' => 'user']);
    }

    /** Test index displays paginated orders */
    public function test_index_displays_paginated_orders(): void
    {
        Order::factory()->count(15)->create(['user_id' => $this->customer->id]);

        $request = new Request();
        $response = $this->controller->index($request);

        $this->assertEquals('admin.orders.index', $response->name());
        $orders = $response->getData()['order'];
        $this->assertEquals(10, $orders->perPage());
    }

    /** Test index search by order ID */
    public function test_index_search_by_order_id(): void
    {
        $order1 = Order::factory()->create(['user_id' => $this->customer->id]);
        Order::factory()->create(['user_id' => $this->customer->id]);

        $request = new Request(['search' => $order1->id]);
        $response = $this->controller->index($request);

        $orders = $response->getData()['order'];
        $this->assertEquals(1, $orders->total());
    }

    /** Test index search by user name */
    public function test_index_search_by_user_name(): void
    {
        $user = User::factory()->create(['name' => 'John Doe', 'role' => 'user']);
        Order::factory()->create(['user_id' => $user->id]);
        Order::factory()->create(['user_id' => $this->customer->id]);

        $request = new Request(['search' => 'John']);
        $response = $this->controller->index($request);

        $orders = $response->getData()['order'];
        $this->assertGreaterThanOrEqual(1, $orders->total());
    }

    /** Test index search by total price */
    public function test_index_search_by_total_price(): void
    {
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'total_price' => 150.50,
        ]);

        $request = new Request(['search' => '150']);
        $response = $this->controller->index($request);

        $orders = $response->getData()['order'];
        $this->assertGreaterThanOrEqual(1, $orders->total());
    }

    /** Test index filter by status */
    public function test_index_filter_by_status(): void
    {
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
        ]);
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'processing',
        ]);

        $request = new Request(['status' => 'pending']);
        $response = $this->controller->index($request);

        $orders = $response->getData()['order'];
        foreach ($orders as $order) {
            $this->assertEquals('pending', $order->status);
        }
    }

    /** Test index orders sorted by created_at */
    public function test_index_orders_by_created_at_desc(): void
    {
        $o1 = Order::factory()->create([
            'user_id' => $this->customer->id,
            'created_at' => now()->subDays(2),
        ]);
        $o2 = Order::factory()->create([
            'user_id' => $this->customer->id,
            'created_at' => now()->subDay(),
        ]);

        $response = $this->controller->index(new Request());
        $orders = $response->getData()['order'];

        $this->assertTrue($orders[0]->created_at->gte($orders[1]->created_at));
    }

    /** Test index eager loads user */
    public function test_index_eager_loads_user_relationship(): void
    {
        Order::factory()->count(3)->create(['user_id' => $this->customer->id]);

        $response = $this->controller->index(new Request());
        $orders = $response->getData()['order'];

        $this->assertTrue($orders->first()->relationLoaded('user'));
    }

    /** Test index provides payment data */
    // public function test_index_provides_payment_data(): void
    // {
    //     Payment::factory()->count(3)->create();

    //     $response = $this->controller->index(new Request());
    //     $payments = $response->getData()['payment'];

    //     $this->assertCount(3, $payments);
    // }

    // /** Test show includes relationships */
    // public function test_show_displays_order_with_details(): void
    // {
    //     $address = Address::factory()->create(['user_id' => $this->customer->id]);
    //     $order = Order::factory()->create([
    //         'user_id' => $this->customer->id,
    //         'address_id' => $address->id,
    //     ]);

    //     $product = Product::factory()->create();
    //     OrderDetail::factory()->create([
    //         'order_id' => $order->id,
    //         'product_id' => $product->id,
    //     ]);

    //     $response = $this->controller->show($order->id);

    //     $this->assertEquals('admin.orders.show', $response->name());

    //     $viewOrder = $response->getData()['order'];
    //     $this->assertTrue($viewOrder->relationLoaded('orderDetail'));
    //     $this->assertTrue($viewOrder->relationLoaded('user'));
    //     $this->assertTrue($viewOrder->relationLoaded('address'));
    // }

    // /** Test show loads payment info */
    // public function test_show_provides_payment_information(): void
    // {
    //     $order = Order::factory()->create(['user_id' => $this->customer->id]);
    //     Payment::factory()->create(['order_id' => $order->id]);

    //     $response = $this->controller->show($order->id);

    //     $payments = $response->getData()['payment'];
    //     $this->assertCount(1, $payments);
    // }

    /** Confirm: update status */
    public function test_confirm_updates_order_status_to_processing(): void
    {
        Notification::fake();

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
        ]);

        $response = $this->controller->confirm($order);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $order->refresh();
        $this->assertEquals('processing', $order->status);
    }

    /** Confirm: notify admins */
    public function test_confirm_sends_notification_to_admins(): void
    {
        Notification::fake();

        $admin2 = User::factory()->create(['role' => 'admin']);
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
        ]);

        $this->controller->confirm($order);

        Notification::assertSentTo([$this->admin, $admin2], OrderConfirmedNotification::class);
    }

    /** Confirm: notify customer */
    public function test_confirm_sends_notification_to_customer(): void
    {
        Notification::fake();

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
        ]);

        $this->controller->confirm($order);

        Notification::assertSentTo($this->customer, CustomerOrderConfirmedNotification::class);
    }

    /** Confirm: locale */
    public function test_confirm_uses_session_locale_for_customer(): void
    {
        Notification::fake();
        Session::put('locale', 'vi');

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
        ]);

        $this->controller->confirm($order);

        Notification::assertSentTo($this->customer, CustomerOrderConfirmedNotification::class);
    }

    /**
     * Test confirm rejects non-pending orders
     */
    // public function test_confirm_rejects_non_pending_orders(): void
    // {
    //     $order = Order::factory()->create([
    //         'user_id' => $this->customer->id,
    //         'status' => 'processing',
    //     ]);

    //     $response = $this->controller->confirm($order);

    //     $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
    //     $order->refresh();
    //     $this->assertEquals('processing', $order->status);
    // }

    /** Shipped updates status */
    public function test_shipped_updates_order_status_to_shipped(): void
    {
        Notification::fake();

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'processing',
        ]);

        $response = $this->controller->shipped($order);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $order->refresh();
        $this->assertEquals('shipped', $order->status);
    }

    /** Shipped: sends notifications */
    public function test_shipped_sends_notifications(): void
    {
        Notification::fake();

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'processing',
        ]);

        $this->controller->shipped($order);

        Notification::assertSentTo($this->admin, OrderShippedNotification::class);
        Notification::assertSentTo($this->customer, CustomerOrderShippedNotification::class);
    }

    /**
     * Test shipped rejects non-processing orders
     */
    // public function test_shipped_rejects_non_processing_orders(): void
    // {
    //     $order = Order::factory()->create([
    //         'user_id' => $this->customer->id,
    //         'status' => 'pending',
    //     ]);

    //     $response = $this->controller->shipped($order);

    //     $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
    //     $order->refresh();
    //     $this->assertEquals('pending', $order->status);
    // }

    // /** Paid: update payment */
    // public function test_paid_updates_payment_status_to_completed(): void
    // {
    //     Notification::fake();

    //     $order = Order::factory()->create(['user_id' => $this->customer->id]);
    //     $payment = Payment::factory()->create([
    //         'order_id' => $order->id,
    //         'payment_status' => 'pending',
    //     ]);

    //     $response = $this->controller->paid($order);

    //     $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

    //     $payment->refresh();
    //     $this->assertEquals('completed', $payment->payment_status);
    // }

    // /** Paid notify admin */
    // public function test_paid_sends_notification_to_admins(): void
    // {
    //     Notification::fake();

    //     $order = Order::factory()->create(['user_id' => $this->customer->id]);
    //     Payment::factory()->create(['order_id' => $order->id]);

    //     $this->controller->paid($order);

    //     Notification::assertSentTo($this->admin, OrderPaidNotification::class);
    // }

    // /** Paid notify customer */
    // public function test_paid_sends_notification_to_customer(): void
    // {
    //     Notification::fake();

    //     $order = Order::factory()->create(['user_id' => $this->customer->id]);
    //     Payment::factory()->create(['order_id' => $order->id]);

    //     $this->controller->paid($order);

    //     Notification::assertSentTo($this->customer, CustomerOrderCompletedNotification::class);
    // }

    /** Paid error when no payment */
    public function test_paid_returns_error_when_payment_not_found(): void
    {
        $order = Order::factory()->create(['user_id' => $this->customer->id]);

        $response = $this->controller->paid($order);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
    }

    /** Cancel: update status */
    public function test_cancel_updates_order_status_to_cancelled(): void
    {
        Notification::fake();

        $order = Order::factory()->create(['user_id' => $this->customer->id]);

        $response = $this->controller->cancel($order->id);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
    }

    /** Cancel: payment failed */
    // public function test_cancel_updates_payment_status_to_failed(): void
    // {
    //     Notification::fake();

    //     $order = Order::factory()->create(['user_id' => $this->customer->id]);
    //     $payment = Payment::factory()->create([
    //         'order_id' => $order->id,
    //         'payment_status' => 'pending',
    //     ]);

    //     $this->controller->cancel($order->id);

    //     $payment->refresh();
    //     $this->assertEquals('failed', $payment->payment_status);
    // }

    // /** Cancel restores stock */
    // public function test_cancel_restores_product_stock(): void
    // {
    //     Notification::fake();

    //     $product = Product::factory()->create(['stock_quantity' => 100]);
    //     $order = Order::factory()->create(['user_id' => $this->customer->id]);

    //     OrderDetail::factory()->create([
    //         'order_id' => $order->id,
    //         'product_id' => $product->id,
    //         'quantity' => 5,
    //     ]);

    //     $this->controller->cancel($order->id);

    //     $product->refresh();
    //     $this->assertEquals(105, $product->stock_quantity);
    // }

    // /** Cancel multiple stock */
    // public function test_cancel_restores_stock_for_multiple_products(): void
    // {
    //     Notification::fake();

    //     $p1 = Product::factory()->create(['stock_quantity' => 50]);
    //     $p2 = Product::factory()->create(['stock_quantity' => 30]);

    //     $order = Order::factory()->create(['user_id' => $this->customer->id]);

    //     OrderDetail::factory()->create([
    //         'order_id' => $order->id,
    //         'product_id' => $p1->id,
    //         'quantity' => 3,
    //     ]);
    //     OrderDetail::factory()->create([
    //         'order_id' => $order->id,
    //         'product_id' => $p2->id,
    //         'quantity' => 2,
    //     ]);

    //     $this->controller->cancel($order->id);

    //     $p1->refresh();
    //     $p2->refresh();

    //     $this->assertEquals(53, $p1->stock_quantity);
    //     $this->assertEquals(32, $p2->stock_quantity);
    // }

    /** Cancel notify admins */
    public function test_cancel_sends_notification_to_admins(): void
    {
        Notification::fake();

        $order = Order::factory()->create(['user_id' => $this->customer->id]);

        $this->controller->cancel($order->id);

        Notification::assertSentTo($this->admin, OrderCancelledNotification::class);
    }

    /** Cancel notify customer */
    public function test_cancel_sends_notification_to_customer(): void
    {
        Notification::fake();

        $order = Order::factory()->create(['user_id' => $this->customer->id]);

        $this->controller->cancel($order->id);

        Notification::assertSentTo($this->customer, CustomerOrderCancelledNotification::class);
    }

    /** Cancel: no payment */
    public function test_cancel_works_when_no_payment_exists(): void
    {
        Notification::fake();

        $order = Order::factory()->create(['user_id' => $this->customer->id]);

        $response = $this->controller->cancel($order->id);

        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
    }

    /** Index: empty search returns all */
    public function test_index_with_empty_search_returns_all_orders(): void
    {
        Order::factory()->count(5)->create(['user_id' => $this->customer->id]);

        $response = $this->controller->index(new Request(['search' => '']));

        $orders = $response->getData()['order'];
        $this->assertEquals(5, $orders->total());
    }

    /** Index: empty status returns all */
    public function test_index_with_null_status_shows_all_statuses(): void
    {
        Order::factory()->create(['user_id' => $this->customer->id, 'status' => 'pending']);
        Order::factory()->create(['user_id' => $this->customer->id, 'status' => 'processing']);

        $response = $this->controller->index(new Request(['status' => '']));

        $orders = $response->getData()['order'];
        $this->assertEquals(2, $orders->total());
    }
}
