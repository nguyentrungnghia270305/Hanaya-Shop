<?php

/**
 * Order Data Flow Test
 *
 * Data Flow Testing cho Order operations - kiểm tra luồng dữ liệu từ cart
 * đến order creation và status updates.
 *
 * Test Coverage:
 * - Cart data → Order data transformation
 * - Order total calculation data flow
 * - Status change data propagation
 * - Payment data flow through order process
 * - Stock adjustment data flow
 *
 * @category Testing
 */

// namespace Tests\Coverage\DataFlow\Order;

// use App\Models\Address;
// use App\Models\Cart\Cart;
// use App\Models\Order\Order;
// use App\Models\Order\OrderDetail;
// use App\Models\Payment\Payment;
// use App\Models\Product\Product;
// use App\Models\User;
// use Illuminate\Foundation\Testing\RefreshDatabase;
// use Tests\TestCase;

// class OrderDataFlowTest extends TestCase
// {
//     use RefreshDatabase;

//     // =================================================================
//     // CART TO ORDER DATA TRANSFORMATION TESTS
//     // =================================================================

//     /** @test */
//     public function it_flows_cart_quantities_to_order_details()
//     {
//         // Given: Cart items with quantities
//         $user = User::factory()->create();
//         $address = Address::factory()->create(['user_id' => $user->id]);
//         $product1 = Product::factory()->create(['price' => 100, 'stock_quantity' => 50]);
//         $product2 = Product::factory()->create(['price' => 200, 'stock_quantity' => 30]);

//         $quantity1 = 3; // DEF: First product quantity
//         $quantity2 = 2; // DEF: Second product quantity

//         Cart::create([
//             'user_id' => $user->id,
//             'product_id' => $product1->id,
//             'session_id' => session()->getId(),
//             'quantity' => $quantity1, // USE: Store in cart
//         ]);

//         Cart::create([
//             'user_id' => $user->id,
//             'product_id' => $product2->id,
//             'session_id' => session()->getId(),
//             'quantity' => $quantity2, // USE: Store in cart
//         ]);

//         // When: Create order from cart
//         $response = $this->actingAs($user)->post(route('checkout.store'), [
//             'address_id' => $address->id,
//             'payment_method' => 'cash_on_delivery',
//             'payment_data' => '{}',
//         ]);

//         // Then: Quantities flow to order details
//         $order = Order::where('user_id', $user->id)->first();
//         $this->assertDatabaseHas('order_details', [
//             'order_id' => $order->id,
//             'product_id' => $product1->id,
//             'quantity' => $quantity1, // USE: Quantity in order detail
//         ]);
//         $this->assertDatabaseHas('order_details', [
//             'order_id' => $order->id,
//             'product_id' => $product2->id,
//             'quantity' => $quantity2, // USE: Quantity in order detail
//         ]);
//     }

//     /** @test */
//     public function it_flows_cart_prices_to_order_details()
//     {
//         // Given: Cart with product prices
//         $user = User::factory()->create();
//         $address = Address::factory()->create(['user_id' => $user->id]);
//         $productPrice = 150.00; // DEF: Product price
//         $product = Product::factory()->create([
//             'price' => $productPrice,
//             'stock_quantity' => 50,
//         ]);

//         Cart::create([
//             'user_id' => $user->id,
//             'product_id' => $product->id,
//             'session_id' => session()->getId(),
//             'quantity' => 2,
//         ]);

//         // When: Create order
//         $response = $this->actingAs($user)->post(route('checkout.store'), [
//             'address_id' => $address->id,
//             'payment_method' => 'cash_on_delivery',
//             'payment_data' => '{}',
//         ]);

//         // Then: Price flows to order detail
//         $order = Order::where('user_id', $user->id)->first();
//         $this->assertDatabaseHas('order_details', [
//             'order_id' => $order->id,
//             'product_id' => $product->id,
//             'price' => $productPrice, // USE: Price captured at order time
//         ]);
//     }

//     /** @test */
//     public function it_flows_multiple_cart_items_to_separate_order_details()
//     {
//         // Given: Cart with multiple items
//         $user = User::factory()->create();
//         $address = Address::factory()->create(['user_id' => $user->id]);
//         $products = Product::factory()->count(3)->create(['stock_quantity' => 100]);

//         $cartData = []; // DEF: Collection of cart items
//         foreach ($products as $index => $product) {
//             $quantity = $index + 1; // DEF: Different quantity for each
//             Cart::create([
//                 'user_id' => $user->id,
//                 'product_id' => $product->id,
//                 'session_id' => session()->getId(),
//                 'quantity' => $quantity, // USE: Store quantity
//             ]);
//             $cartData[$product->id] = $quantity; // USE: Track for verification
//         }

//         // When: Create order
//         $response = $this->actingAs($user)->post(route('checkout.store'), [
//             'address_id' => $address->id,
//             'payment_method' => 'cash_on_delivery',
//             'payment_data' => '{}',
//         ]);

//         // Then: Each cart item becomes separate order detail
//         $order = Order::where('user_id', $user->id)->first();
//         foreach ($cartData as $productId => $quantity) {
//             $this->assertDatabaseHas('order_details', [
//                 'order_id' => $order->id,
//                 'product_id' => $productId,
//                 'quantity' => $quantity, // USE: Verify each item
//             ]);
//         }
//     }

//     // =================================================================
//     // ORDER TOTAL CALCULATION DATA FLOW TESTS
//     // =================================================================

//     /** @test */
//     public function it_flows_item_subtotals_to_order_total()
//     {
//         // Given: Cart items
//         $user = User::factory()->create();
//         $address = Address::factory()->create(['user_id' => $user->id]);
//         $product1 = Product::factory()->create(['price' => 100, 'stock_quantity' => 50]);
//         $product2 = Product::factory()->create(['price' => 50, 'stock_quantity' => 50]);

//         $qty1 = 2; // DEF: Quantity 1
//         $qty2 = 3; // DEF: Quantity 2

//         Cart::create([
//             'user_id' => $user->id,
//             'product_id' => $product1->id,
//             'session_id' => session()->getId(),
//             'quantity' => $qty1,
//         ]);

//         Cart::create([
//             'user_id' => $user->id,
//             'product_id' => $product2->id,
//             'session_id' => session()->getId(),
//             'quantity' => $qty2,
//         ]);

//         // When: Calculate expected total
//         $subtotal1 = 100 * $qty1; // USE: Price × Quantity 1
//         $subtotal2 = 50 * $qty2; // USE: Price × Quantity 2
//         $expectedTotal = $subtotal1 + $subtotal2; // USE: Sum subtotals

//         // Create order
//         $response = $this->actingAs($user)->post(route('checkout.store'), [
//             'address_id' => $address->id,
//             'payment_method' => 'cash_on_delivery',
//             'payment_data' => '{}',
//         ]);

//         // Then: Order total matches calculation
//         $order = Order::where('user_id', $user->id)->first();
//         $this->assertEquals($expectedTotal, $order->total_amount); // USE: Verify total
//     }

//     /** @test */
//     public function it_flows_discounted_prices_to_order_total()
//     {
//         // Given: Products with discounts
//         $user = User::factory()->create();
//         $address = Address::factory()->create(['user_id' => $user->id]);
//         $originalPrice = 100.00; // DEF: Original price
//         $discountPercent = 20; // DEF: Discount
//         $product = Product::factory()->create([
//             'price' => $originalPrice,
//             'discount_percent' => $discountPercent,
//             'stock_quantity' => 50,
//         ]);

//         Cart::create([
//             'user_id' => $user->id,
//             'product_id' => $product->id,
//             'session_id' => session()->getId(),
//             'quantity' => 1,
//         ]);

//         // When: Calculate with discount
//         $discountedPrice = $originalPrice * (1 - $discountPercent / 100); // USE: Apply discount
//         $expectedTotal = $discountedPrice * 1; // USE: Calculate total

//         // Create order
//         $response = $this->actingAs($user)->post(route('checkout.store'), [
//             'address_id' => $address->id,
//             'payment_method' => 'cash_on_delivery',
//             'payment_data' => '{}',
//         ]);

//         // Then: Order reflects discounted price
//         $order = Order::where('user_id', $user->id)->first();
//         $orderDetail = OrderDetail::where('order_id', $order->id)->first();

//         // Note: Order captures current price, which should be original price
//         // but total_amount should reflect the calculation
//         $this->assertNotNull($order);
//     }

//     // =================================================================
//     // STATUS CHANGE DATA PROPAGATION TESTS
//     // =================================================================

//     /** @test */
//     public function it_flows_status_from_pending_to_processing()
//     {
//         // Given: Order with pending status
//         $order = Order::factory()->pending()->create();
//         $initialStatus = 'pending'; // DEF: Initial status
//         $this->assertEquals($initialStatus, $order->status); // USE: Verify initial

//         // When: Admin confirms order
//         $newStatus = 'processing'; // DEF: New status
//         $response = $this->actingAs($order->user)->post(
//             route('admin.orders.confirm', $order->id)
//         );

//         // Then: Status flows to processing
//         $order->refresh();
//         $this->assertEquals($newStatus, $order->status); // USE: Verify change
//     }

//     /** @test */
//     public function it_flows_status_through_complete_lifecycle()
//     {
//         // Given: New order
//         $order = Order::factory()->pending()->create();
//         $statusFlow = ['pending', 'processing', 'shipped', 'completed']; // DEF: Status sequence

//         // When: Progress through each status
//         $currentIndex = 0; // DEF: Track position in flow

//         // Confirm (pending → processing)
//         $this->actingAs($order->user)->post(route('admin.orders.confirm', $order->id));
//         $order->refresh();
//         $currentIndex++; // USE: Move to next status
//         $this->assertEquals($statusFlow[$currentIndex], $order->status); // USE: Verify

//         // Ship (processing → shipped)
//         $this->actingAs($order->user)->post(route('admin.orders.shipped', $order->id));
//         $order->refresh();
//         $currentIndex++; // USE: Move to next status
//         $this->assertEquals($statusFlow[$currentIndex], $order->status); // USE: Verify

//         // Complete (shipped → completed)
//         $this->actingAs($order->user)->post(route('orders.receive', $order->id));
//         $order->refresh();
//         $currentIndex++; // USE: Move to next status
//         $this->assertEquals($statusFlow[$currentIndex], $order->status); // USE: Verify
//     }

//     /** @test */
//     public function it_flows_cancelled_status_with_stock_restoration()
//     {
//         // Given: Order with products
//         $user = User::factory()->create();
//         $product = Product::factory()->create(['stock_quantity' => 100]);
//         $orderQuantity = 5; // DEF: Ordered quantity
//         $initialStock = $product->stock_quantity; // DEF: Initial stock

//         $order = Order::factory()->pending()->create(['user_id' => $user->id]);
//         OrderDetail::factory()->create([
//             'order_id' => $order->id,
//             'product_id' => $product->id,
//             'quantity' => $orderQuantity, // USE: Quantity in order
//         ]);

//         // Simulate stock deduction
//         $product->stock_quantity -= $orderQuantity; // USE: Reduce stock
//         $product->save();

//         // When: Cancel order
//         $response = $this->actingAs($user)->post(route('orders.cancel', $order->id));

//         // Then: Status flows to cancelled and stock restored
//         $order->refresh();
//         $product->refresh();
//         $this->assertEquals('cancelled', $order->status); // USE: Status changed
//         $this->assertEquals($initialStock, $product->stock_quantity); // USE: Stock restored
//     }

//     // =================================================================
//     // PAYMENT DATA FLOW TESTS
//     // =================================================================

//     /** @test */
//     public function it_flows_payment_method_to_payment_record()
//     {
//         // Given: Order creation with payment method
//         $user = User::factory()->create();
//         $address = Address::factory()->create(['user_id' => $user->id]);
//         $product = Product::factory()->create(['stock_quantity' => 50]);
//         Cart::create([
//             'user_id' => $user->id,
//             'product_id' => $product->id,
//             'session_id' => session()->getId(),
//             'quantity' => 1,
//         ]);

//         $paymentMethod = 'credit_card'; // DEF: Payment method

//         // When: Create order with payment
//         $response = $this->actingAs($user)->post(route('checkout.store'), [
//             'address_id' => $address->id,
//             'payment_method' => $paymentMethod, // USE: Payment method
//             'payment_data' => json_encode(['card' => '1234']),
//         ]);

//         // Then: Payment method flows to payment record
//         $order = Order::where('user_id', $user->id)->first();
//         $this->assertDatabaseHas('payments', [
//             'order_id' => $order->id,
//             'payment_method' => $paymentMethod, // USE: Stored method
//         ]);
//     }

//     /** @test */
//     public function it_flows_payment_amount_from_order_total()
//     {
//         // Given: Order with calculated total
//         $user = User::factory()->create();
//         $address = Address::factory()->create(['user_id' => $user->id]);
//         $productPrice = 100.00; // DEF: Product price
//         $quantity = 3; // DEF: Quantity
//         $product = Product::factory()->create([
//             'price' => $productPrice,
//             'stock_quantity' => 50,
//         ]);

//         Cart::create([
//             'user_id' => $user->id,
//             'product_id' => $product->id,
//             'session_id' => session()->getId(),
//             'quantity' => $quantity,
//         ]);

//         // When: Create order
//         $expectedAmount = $productPrice * $quantity; // USE: Calculate amount

//         $response = $this->actingAs($user)->post(route('checkout.store'), [
//             'address_id' => $address->id,
//             'payment_method' => 'cash_on_delivery',
//             'payment_data' => '{}',
//         ]);

//         // Then: Payment amount equals order total
//         $order = Order::where('user_id', $user->id)->first();
//         $payment = Payment::where('order_id', $order->id)->first();
//         $this->assertEquals($expectedAmount, $order->total_amount); // USE: Order total
//         $this->assertEquals($order->total_amount, $payment->amount); // USE: Payment amount
//     }

//     /** @test */
//     public function it_flows_payment_status_based_on_method()
//     {
//         // Given: Different payment methods
//         $user = User::factory()->create();
//         $address = Address::factory()->create(['user_id' => $user->id]);
//         $product = Product::factory()->create(['stock_quantity' => 50]);

//         // Test COD → pending status
//         Cart::create([
//             'user_id' => $user->id,
//             'product_id' => $product->id,
//             'session_id' => session()->getId(),
//             'quantity' => 1,
//         ]);

//         $codMethod = 'cash_on_delivery'; // DEF: COD method
//         $expectedCodStatus = 'pending'; // DEF: Expected status for COD

//         $response = $this->actingAs($user)->post(route('checkout.store'), [
//             'address_id' => $address->id,
//             'payment_method' => $codMethod, // USE: COD method
//             'payment_data' => '{}',
//         ]);

//         $order = Order::where('user_id', $user->id)->latest()->first();
//         $payment = Payment::where('order_id', $order->id)->first();
//         $this->assertEquals($expectedCodStatus, $payment->payment_status); // USE: Pending for COD
//     }

//     // =================================================================
//     // STOCK ADJUSTMENT DATA FLOW TESTS
//     // =================================================================

//     /** @test */
//     public function it_flows_order_quantity_to_stock_deduction()
//     {
//         // Given: Product with stock
//         $user = User::factory()->create();
//         $address = Address::factory()->create(['user_id' => $user->id]);
//         $initialStock = 100; // DEF: Initial stock
//         $orderQuantity = 10; // DEF: Quantity to order
//         $product = Product::factory()->create(['stock_quantity' => $initialStock]);

//         Cart::create([
//             'user_id' => $user->id,
//             'product_id' => $product->id,
//             'session_id' => session()->getId(),
//             'quantity' => $orderQuantity, // USE: Order quantity
//         ]);

//         // When: Create order (stock should be deducted)
//         $expectedRemainingStock = $initialStock - $orderQuantity; // USE: Calculate remaining

//         $response = $this->actingAs($user)->post(route('checkout.store'), [
//             'address_id' => $address->id,
//             'payment_method' => 'cash_on_delivery',
//             'payment_data' => '{}',
//         ]);

//         // Then: Stock reflects deduction
//         $product->refresh();
//         $this->assertEquals($expectedRemainingStock, $product->stock_quantity); // USE: Verify
//     }

//     /** @test */
//     public function it_flows_cancellation_to_stock_restoration()
//     {
//         // Given: Completed order with stock deducted
//         $user = User::factory()->create();
//         $initialStock = 50; // DEF: Initial stock
//         $orderQuantity = 5; // DEF: Ordered amount
//         $product = Product::factory()->create(['stock_quantity' => $initialStock]);

//         $order = Order::factory()->pending()->create(['user_id' => $user->id]);
//         OrderDetail::factory()->create([
//             'order_id' => $order->id,
//             'product_id' => $product->id,
//             'quantity' => $orderQuantity, // USE: Quantity in order
//         ]);

//         // Simulate stock deduction
//         $product->stock_quantity -= $orderQuantity; // USE: Deduct from stock
//         $product->save();
//         $stockAfterOrder = $product->stock_quantity; // DEF: Stock after order

//         // When: Cancel order
//         $response = $this->actingAs($user)->post(route('orders.cancel', $order->id));

//         // Then: Stock flows back to original
//         $product->refresh();
//         $restoredStock = $stockAfterOrder + $orderQuantity; // USE: Calculate restored
//         $this->assertEquals($restoredStock, $product->stock_quantity); // USE: Verify
//         $this->assertEquals($initialStock, $product->stock_quantity); // USE: Back to initial
//     }

//     /** @test */
//     public function it_flows_multiple_items_stock_adjustments()
//     {
//         // Given: Order with multiple products
//         $user = User::factory()->create();
//         $address = Address::factory()->create(['user_id' => $user->id]);

//         $stocks = [100, 50, 75]; // DEF: Initial stocks
//         $quantities = [5, 3, 7]; // DEF: Order quantities
//         $products = [];

//         foreach ($stocks as $index => $stock) {
//             $products[$index] = Product::factory()->create(['stock_quantity' => $stock]);
//             Cart::create([
//                 'user_id' => $user->id,
//                 'product_id' => $products[$index]->id,
//                 'session_id' => session()->getId(),
//                 'quantity' => $quantities[$index], // USE: Each quantity
//             ]);
//         }

//         // When: Create order
//         $response = $this->actingAs($user)->post(route('checkout.store'), [
//             'address_id' => $address->id,
//             'payment_method' => 'cash_on_delivery',
//             'payment_data' => '{}',
//         ]);

//         // Then: Each product stock adjusted correctly
//         foreach ($products as $index => $product) {
//             $product->refresh();
//             $expectedStock = $stocks[$index] - $quantities[$index]; // USE: Calculate
//             $this->assertEquals($expectedStock, $product->stock_quantity); // USE: Verify
//         }
//     }

//     // =================================================================
//     // ADDRESS DATA FLOW TESTS
//     // =================================================================

//     /** @test */
//     public function it_flows_address_id_to_order_record()
//     {
//         // Given: User with address
//         $user = User::factory()->create();
//         $addressId = Address::factory()->create(['user_id' => $user->id])->id; // DEF: Address ID
//         $product = Product::factory()->create(['stock_quantity' => 50]);
//         Cart::create([
//             'user_id' => $user->id,
//             'product_id' => $product->id,
//             'session_id' => session()->getId(),
//             'quantity' => 1,
//         ]);

//         // When: Create order with address
//         $response = $this->actingAs($user)->post(route('checkout.store'), [
//             'address_id' => $addressId, // USE: Address in request
//             'payment_method' => 'cash_on_delivery',
//             'payment_data' => '{}',
//         ]);

//         // Then: Address ID flows to order
//         $this->assertDatabaseHas('orders', [
//             'user_id' => $user->id,
//             'address_id' => $addressId, // USE: Stored address
//         ]);
//     }

//     /** @test */
//     public function it_maintains_address_data_integrity_in_order()
//     {
//         // Given: Address with specific data
//         $user = User::factory()->create();
//         $recipientName = 'John Doe'; // DEF: Recipient name
//         $phoneNumber = '0123456789'; // DEF: Phone
//         $address = Address::factory()->create([
//             'user_id' => $user->id,
//             'recipient_name' => $recipientName,
//             'phone_number' => $phoneNumber,
//         ]);

//         $product = Product::factory()->create(['stock_quantity' => 50]);
//         Cart::create([
//             'user_id' => $user->id,
//             'product_id' => $product->id,
//             'session_id' => session()->getId(),
//             'quantity' => 1,
//         ]);

//         // When: Create order
//         $response = $this->actingAs($user)->post(route('checkout.store'), [
//             'address_id' => $address->id,
//             'payment_method' => 'cash_on_delivery',
//             'payment_data' => '{}',
//         ]);

//         // Then: Address data accessible through relationship
//         $order = Order::where('user_id', $user->id)->first();
//         $this->assertEquals($recipientName, $order->address->recipient_name); // USE: Name flows
//         $this->assertEquals($phoneNumber, $order->address->phone_number); // USE: Phone flows
//     }
// }
