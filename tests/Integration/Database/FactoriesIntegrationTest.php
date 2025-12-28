<?php

// namespace Tests\Integration\Database;

// use App\Models\Address;
// use App\Models\Cart\Cart;
// use App\Models\Order\Order;
// use App\Models\Order\OrderDetail;
// use App\Models\Product\Category;
// use App\Models\Product\Product;
// use App\Models\User;
// use Illuminate\Foundation\Testing\RefreshDatabase;
// use Tests\TestCase;

// class FactoriesIntegrationTest extends TestCase
// {
//     use RefreshDatabase;

//     /** @test */
//     public function user_factory_creates_valid_users()
//     {
//         $user = User::factory()->create();

//         $this->assertDatabaseHas('users', [
//             'id' => $user->id,
//             'email' => $user->email,
//         ]);

//         $this->assertTrue(\Hash::check('password', $user->password));
//     }

//     /** @test */
//     public function user_factory_can_create_admin()
//     {
//         $admin = User::factory()->create(['role' => 'admin']);

//         $this->assertEquals('admin', $admin->role);
//         $this->assertTrue($admin->isAdmin());
//     }

//     /** @test */
//     public function user_factory_can_create_regular_user()
//     {
//         $user = User::factory()->create(['role' => 'user']);

//         $this->assertEquals('user', $user->role);
//         $this->assertTrue($user->isUser());
//     }

//     /** @test */
//     public function address_factory_creates_valid_addresses()
//     {
//         $user = User::factory()->create();
//         $address = Address::factory()->create(['user_id' => $user->id]);

//         $this->assertDatabaseHas('addresses', [
//             'id' => $address->id,
//             'user_id' => $user->id,
//         ]);

//         // Factory uses 'phone_number' and 'address' fields
//         $this->assertNotNull($address->phone_number);
//         $this->assertNotNull($address->address);
//     }

//     /** @test */
//     public function category_factory_creates_valid_categories()
//     {
//         $category = Category::factory()->create();

//         $this->assertDatabaseHas('categories', [
//             'id' => $category->id,
//             'name' => $category->name,
//         ]);

//         $this->assertNotNull($category->name);
//     }

//     /** @test */
//     public function product_factory_creates_valid_products()
//     {
//         $category = Category::factory()->create();
//         $product = Product::factory()->create(['category_id' => $category->id]);

//         $this->assertDatabaseHas('products', [
//             'id' => $product->id,
//             'category_id' => $category->id,
//         ]);

//         $this->assertGreaterThan(0, $product->price);
//         $this->assertGreaterThanOrEqual(0, $product->stock);
//     }

//     /** @test */
//     public function cart_factory_creates_valid_cart_items()
//     {
//         $user = User::factory()->create();
//         $category = Category::factory()->create();
//         $product = Product::factory()->create(['category_id' => $category->id]);
        
//         $cart = Cart::factory()->create([
//             'user_id' => $user->id,
//             'product_id' => $product->id,
//         ]);

//         $this->assertDatabaseHas('carts', [
//             'id' => $cart->id,
//             'user_id' => $user->id,
//             'product_id' => $product->id,
//         ]);

//         $this->assertGreaterThan(0, $cart->quantity);
//     }

//     /** @test */
//     public function order_factory_creates_valid_orders()
//     {
//         $user = User::factory()->create();
//         $order = Order::factory()->create(['user_id' => $user->id]);

//         $this->assertDatabaseHas('orders', [
//             'id' => $order->id,
//             'user_id' => $user->id,
//         ]);

//         $this->assertGreaterThan(0, $order->total_amount);
//         $this->assertNotNull($order->status);
//     }

//     /** @test */
//     public function order_detail_factory_creates_valid_order_details()
//     {
//         $user = User::factory()->create();
//         $order = Order::factory()->create(['user_id' => $user->id]);
//         $category = Category::factory()->create();
//         $product = Product::factory()->create(['category_id' => $category->id]);

//         $orderDetail = OrderDetail::factory()->create([
//             'order_id' => $order->id,
//             'product_id' => $product->id,
//         ]);

//         $this->assertDatabaseHas('order_details', [
//             'id' => $orderDetail->id,
//             'order_id' => $order->id,
//             'product_id' => $product->id,
//         ]);

//         $this->assertGreaterThan(0, $orderDetail->quantity);
//         $this->assertGreaterThan(0, $orderDetail->price);
//     }

//     /** @test */
//     public function factories_can_create_multiple_records()
//     {
//         $users = User::factory()->count(5)->create();
//         $this->assertCount(5, $users);

//         $categories = Category::factory()->count(3)->create();
//         $this->assertCount(3, $categories);

//         $products = Product::factory()->count(10)->create([
//             'category_id' => $categories->first()->id
//         ]);
//         $this->assertCount(10, $products);
//     }

//     /** @test */
//     public function factories_support_state_customization()
//     {
//         $verifiedUser = User::factory()->create([
//             'email_verified_at' => now(),
//         ]);
//         $this->assertNotNull($verifiedUser->email_verified_at);

//         $unverifiedUser = User::factory()->create([
//             'email_verified_at' => null,
//         ]);
//         $this->assertNull($unverifiedUser->email_verified_at);
//     }

//     /** @test */
//     public function order_detail_factory_stores_price_and_quantity()
//     {
//         $user = User::factory()->create();
//         $order = Order::factory()->create(['user_id' => $user->id]);
//         $category = Category::factory()->create();
//         $product = Product::factory()->create(['category_id' => $category->id, 'price' => 100]);

//         $orderDetail = OrderDetail::factory()->create([
//             'order_id' => $order->id,
//             'product_id' => $product->id,
//             'quantity' => 3,
//             'price' => 100,
//         ]);

//         $this->assertEquals(3, $orderDetail->quantity);
//         $this->assertEquals(100, $orderDetail->price);
//         $this->assertEquals(300, $orderDetail->quantity * $orderDetail->price);
//     }
// }
