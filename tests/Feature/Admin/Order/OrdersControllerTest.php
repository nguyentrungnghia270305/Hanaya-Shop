<?php

namespace Tests\Feature\Admin\Order;

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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class OrdersControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->customer = User::factory()->create(['role' => 'user']);
        $this->actingAs($this->admin);
    }

    /**
     * Test index displays paginated orders
     */
    public function test_index_displays_paginated_orders(): void
    {
        Order::factory()->count(15)->create(['user_id' => $this->customer->id]);

        $response = $this->get(route('admin.order'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.orders.index');
        $response->assertViewHas('order');

        $orders = $response->viewData('order');
        $this->assertEquals(10, $orders->perPage());
    }

    /**
     * Test index with search by order ID
     */
    public function test_index_search_by_order_id(): void
    {
        Order::query()->delete();

        $order1 = Order::factory()->create(['user_id' => $this->customer->id]);
        Order::factory()->create(['user_id' => $this->customer->id]);

        $response = $this->get(route('admin.order', ['search' => $order1->id]));

        $response->assertStatus(200);
        $orders = $response->viewData('order');
        $this->assertEquals(1, $orders->total());
    }

    /**
     * Test index with search by user name
     */
    public function test_index_search_by_user_name(): void
    {
        $user = User::factory()->create(['name' => 'John Doe', 'role' => 'user']);
        Order::factory()->create(['user_id' => $user->id]);
        Order::factory()->create(['user_id' => $this->customer->id]);

        $response = $this->get(route('admin.order', ['search' => 'John']));

        $response->assertStatus(200);
        $orders = $response->viewData('order');
        $this->assertGreaterThanOrEqual(1, $orders->total());
    }

    /**
     * Test index with search by total price
     */
    public function test_index_search_by_total_price(): void
    {
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'total_price' => 150.50,
        ]);

        $response = $this->get(route('admin.order', ['search' => '150']));

        $response->assertStatus(200);
        $orders = $response->viewData('order');
        $this->assertGreaterThanOrEqual(1, $orders->total());
    }

    /**
     * Test index with status filter
     */
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

        $response = $this->get(route('admin.order', ['status' => 'pending']));

        $response->assertStatus(200);
        $orders = $response->viewData('order');
        foreach ($orders as $order) {
            $this->assertEquals('pending', $order->status);
        }
    }

    /**
     * Test index orders by created_at descending
     */
    public function test_index_orders_by_created_at_desc(): void
    {
        $order1 = Order::factory()->create([
            'user_id' => $this->customer->id,
            'created_at' => now()->subDays(2),
        ]);
        $order2 = Order::factory()->create([
            'user_id' => $this->customer->id,
            'created_at' => now()->subDay(),
        ]);

        $response = $this->get(route('admin.order'));

        $orders = $response->viewData('order');
        $this->assertTrue($orders[0]->created_at->gte($orders[1]->created_at));
    }

    /**
     * Test index eager loads user relationship
     */
    public function test_index_eager_loads_user_relationship(): void
    {
        Order::factory()->count(3)->create(['user_id' => $this->customer->id]);

        $response = $this->get(route('admin.order'));

        $orders = $response->viewData('order');
        $this->assertTrue($orders->first()->relationLoaded('user'));
    }

    /**
     * Test index provides payment data
     */
    // public function test_index_provides_payment_data(): void
    // {
    //     Payment::factory()->count(3)->create();

    //     $response = $this->get(route('admin.order'));

    //     $response->assertViewHas('payment');
    //     $payments = $response->viewData('payment');
    //     $this->assertCount(3, $payments);
    // }

    /**
     * Test show displays order with details
     */
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

    //     $response = $this->get(route('admin.order.show', $order->id));

    //     $response->assertStatus(200);
    //     $response->assertViewIs('admin.orders.show');
    //     $response->assertViewHas('order');

    //     $viewOrder = $response->viewData('order');
    //     $this->assertTrue($viewOrder->relationLoaded('orderDetail'));
    //     $this->assertTrue($viewOrder->relationLoaded('user'));
    //     $this->assertTrue($viewOrder->relationLoaded('address'));
    // }

    /**
     * Test show returns 404 for non-existent order
     */
    public function test_show_returns_404_for_non_existent_order(): void
    {
        $response = $this->get(route('admin.order.show', 999));

        $response->assertStatus(404);
    }

    /**
     * Test show provides payment information
     */
    // public function test_show_provides_payment_information(): void
    // {
    //     $order = Order::factory()->create(['user_id' => $this->customer->id]);
    //     Payment::factory()->create(['order_id' => $order->id]);

    //     $response = $this->get(route('admin.order.show', $order->id));

    //     $response->assertViewHas('payment');
    //     $payments = $response->viewData('payment');
    //     $this->assertCount(1, $payments);
    // }

    /**
     * Test confirm updates order status to processing
     */
    public function test_confirm_updates_order_status_to_processing(): void
    {
        Notification::fake();

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
        ]);

        $response = $this->put(route('admin.order.confirm', $order->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('processing', $order->status);
    }

    /**
     * Test confirm sends notification to admins
     */
    public function test_confirm_sends_notification_to_admins(): void
    {
        Notification::fake();

        $admin2 = User::factory()->create(['role' => 'admin']);
        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
        ]);

        $this->put(route('admin.order.confirm', $order->id));

        Notification::assertSentTo(
            [$this->admin, $admin2],
            OrderConfirmedNotification::class
        );
    }

    /**
     * Test confirm sends notification to customer
     */
    public function test_confirm_sends_notification_to_customer(): void
    {
        Notification::fake();

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
        ]);

        $this->put(route('admin.order.confirm', $order->id));

        Notification::assertSentTo(
            $this->customer,
            CustomerOrderConfirmedNotification::class
        );
    }

    /**
     * Test confirm uses session locale for customer notification
     */
    public function test_confirm_uses_session_locale_for_customer(): void
    {
        Notification::fake();
        Session::put('locale', 'vi');

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
        ]);

        $this->put(route('admin.order.confirm', $order->id));

        Notification::assertSentTo(
            $this->customer,
            CustomerOrderConfirmedNotification::class,
            function ($notification, $channels, $notifiable) {
                return $notification->locale === 'vi';
            }
        );
    }

    /**
     * Test shipped updates order status to shipped
     */
    public function test_shipped_updates_order_status_to_shipped(): void
    {
        Notification::fake();

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'processing',
        ]);

        $response = $this->put(route('admin.order.shipped', $order->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('shipped', $order->status);
    }

    /**
     * Test shipped sends notifications to admins and customer
     */
    public function test_shipped_sends_notifications(): void
    {
        Notification::fake();

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'processing',
        ]);

        $this->put(route('admin.order.shipped', $order->id));

        Notification::assertSentTo($this->admin, OrderShippedNotification::class);
        Notification::assertSentTo($this->customer, CustomerOrderShippedNotification::class);
    }

    /**
     * Test paid updates payment status to completed
     */
    // public function test_paid_updates_payment_status_to_completed(): void
    // {
    //     Notification::fake();

    //     $order = Order::factory()->create(['user_id' => $this->customer->id]);
    //     $payment = Payment::factory()->create([
    //         'order_id' => $order->id,
    //         'payment_status' => 'pending',
    //     ]);

    //     $response = $this->put(route('admin.order.paid', $order->id));

    //     $response->assertRedirect();
    //     $response->assertSessionHas('success');

    //     $payment->refresh();
    //     $this->assertEquals('completed', $payment->payment_status);
    // }

    /**
     * Test paid sends notifications to admins
     */
    // public function test_paid_sends_notification_to_admins(): void
    // {
    //     Notification::fake();

    //     $order = Order::factory()->create(['user_id' => $this->customer->id]);
    //     Payment::factory()->create(['order_id' => $order->id]);

    //     $this->put(route('admin.order.paid', $order->id));

    //     Notification::assertSentTo($this->admin, OrderPaidNotification::class);
    // }

    /**
     * Test paid sends notification to customer
     */
    // public function test_paid_sends_notification_to_customer(): void
    // {
    //     Notification::fake();

    //     $order = Order::factory()->create(['user_id' => $this->customer->id]);
    //     Payment::factory()->create(['order_id' => $order->id]);

    //     $this->put(route('admin.order.paid', $order->id));

    //     Notification::assertSentTo($this->customer, CustomerOrderCompletedNotification::class);
    // }

    /**
     * Test paid returns error when payment not found
     */
    public function test_paid_returns_error_when_payment_not_found(): void
    {
        $order = Order::factory()->create(['user_id' => $this->customer->id]);

        $response = $this->put(route('admin.order.paid', $order->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test paid uses database transaction
     */
    // public function test_paid_uses_database_transaction(): void
    // {
    //     Notification::fake();

    //     $order = Order::factory()->create(['user_id' => $this->customer->id]);
    //     $payment = Payment::factory()->create([
    //         'order_id' => $order->id,
    //         'payment_status' => 'pending',
    //     ]);

    //     $this->put(route('admin.order.paid', $order->id));

    //     // Verify transaction was successful
    //     $payment->refresh();
    //     $this->assertEquals('completed', $payment->payment_status);
    // }

    /**
     * Test cancel updates order status to cancelled
     */
    public function test_cancel_updates_order_status_to_cancelled(): void
    {
        Notification::fake();

        $order = Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'pending',
        ]);

        $response = $this->put(route('admin.orders.cancel', $order->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
    }

    /**
     * Test cancel updates payment status to failed
     */
    // public function test_cancel_updates_payment_status_to_failed(): void
    // {
    //     Notification::fake();

    //     $order = Order::factory()->create(['user_id' => $this->customer->id]);
    //     $payment = Payment::factory()->create([
    //         'order_id' => $order->id,
    //         'payment_status' => 'pending',
    //     ]);

    //     $this->put(route('admin.orders.cancel', $order->id));

    //     $payment->refresh();
    //     $this->assertEquals('failed', $payment->payment_status);
    // }

    // /**
    //  * Test cancel restores product stock
    //  */
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

    //     $this->put(route('admin.orders.cancel', $order->id));

    //     $product->refresh();
    //     $this->assertEquals(105, $product->stock_quantity);
    // }

    // /**
    //  * Test cancel restores stock for multiple products
    //  */
    // public function test_cancel_restores_stock_for_multiple_products(): void
    // {
    //     Notification::fake();

    //     $product1 = Product::factory()->create(['stock_quantity' => 50]);
    //     $product2 = Product::factory()->create(['stock_quantity' => 30]);
    //     $order = Order::factory()->create(['user_id' => $this->customer->id]);

    //     OrderDetail::factory()->create([
    //         'order_id' => $order->id,
    //         'product_id' => $product1->id,
    //         'quantity' => 3,
    //     ]);
    //     OrderDetail::factory()->create([
    //         'order_id' => $order->id,
    //         'product_id' => $product2->id,
    //         'quantity' => 2,
    //     ]);

    //     $this->put(route('admin.orders.cancel', $order->id));

    //     $product1->refresh();
    //     $product2->refresh();
    //     $this->assertEquals(53, $product1->stock_quantity);
    //     $this->assertEquals(32, $product2->stock_quantity);
    // }

    /**
     * Test cancel sends notifications to admins
     */
    public function test_cancel_sends_notification_to_admins(): void
    {
        Notification::fake();

        $order = Order::factory()->create(['user_id' => $this->customer->id]);

        $this->put(route('admin.orders.cancel', $order->id));

        Notification::assertSentTo($this->admin, OrderCancelledNotification::class);
    }

    /**
     * Test cancel sends notification to customer
     */
    public function test_cancel_sends_notification_to_customer(): void
    {
        Notification::fake();

        $order = Order::factory()->create(['user_id' => $this->customer->id]);

        $this->put(route('admin.orders.cancel', $order->id));

        Notification::assertSentTo($this->customer, CustomerOrderCancelledNotification::class);
    }

    /**
     * Test cancel uses database transaction
     */
    // public function test_cancel_uses_database_transaction(): void
    // {
    //     Notification::fake();

    //     $product = Product::factory()->create(['stock_quantity' => 100]);
    //     $order = Order::factory()->create(['user_id' => $this->customer->id]);
    //     OrderDetail::factory()->create([
    //         'order_id' => $order->id,
    //         'product_id' => $product->id,
    //         'quantity' => 5,
    //     ]);

    //     $this->put(route('admin.orders.cancel', $order->id));

    //     // All changes should be committed together
    //     $order->refresh();
    //     $product->refresh();
    //     $this->assertEquals('cancelled', $order->status);
    //     $this->assertEquals(105, $product->stock_quantity);
    // }

    /**
     * Test cancel returns 404 for non-existent order
     */
    public function test_cancel_returns_404_for_non_existent_order(): void
    {
        $response = $this->put(route('admin.orders.cancel', 999));

        $response->assertStatus(404);
    }

    /**
     * Test routes require authentication
     */
    public function test_routes_require_authentication(): void
    {
        Auth::logout();

        $order = Order::factory()->create(['user_id' => $this->customer->id]);

        $this->get(route('admin.order'))->assertRedirect(route('login'));
        $this->get(route('admin.order.show', $order->id))->assertRedirect(route('login'));
        $this->put(route('admin.order.confirm', $order->id))->assertRedirect(route('login'));
        $this->put(route('admin.order.shipped', $order->id))->assertRedirect(route('login'));
        $this->put(route('admin.order.paid', $order->id))->assertRedirect(route('login'));
        $this->put(route('admin.orders.cancel', $order->id))->assertRedirect(route('login'));
    }

    /**
     * Test index with empty search returns all orders
     */
    public function test_index_with_empty_search_returns_all_orders(): void
    {
        Order::factory()->count(5)->create(['user_id' => $this->customer->id]);

        $response = $this->get(route('admin.order', ['search' => '']));

        $response->assertStatus(200);
        $orders = $response->viewData('order');
        $this->assertEquals(5, $orders->total());
    }

    /**
     * Test index with null status shows all statuses
     */
    public function test_index_with_null_status_shows_all_statuses(): void
    {
        Order::factory()->create(['user_id' => $this->customer->id, 'status' => 'pending']);
        Order::factory()->create(['user_id' => $this->customer->id, 'status' => 'processing']);

        $response = $this->get(route('admin.order', ['status' => '']));

        $response->assertStatus(200);
        $orders = $response->viewData('order');
        $this->assertEquals(2, $orders->total());
    }

    /**
     * Test cancel works when no payment exists
     */
    public function test_cancel_works_when_no_payment_exists(): void
    {
        Notification::fake();

        $order = Order::factory()->create(['user_id' => $this->customer->id]);

        $response = $this->put(route('admin.orders.cancel', $order->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
    }
}
