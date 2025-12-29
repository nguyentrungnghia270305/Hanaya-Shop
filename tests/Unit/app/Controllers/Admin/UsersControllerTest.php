<?php

namespace Tests\Unit\App\Controllers\Admin;

use App\Models\Cart\Cart;
use App\Models\Order\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\ControllerTestCase;

class UsersControllerTest extends ControllerTestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin);
    }

    /**
     * Test index displays paginated users excluding current admin
     */
    public function test_index_displays_paginated_users_excluding_current_admin(): void
    {
        User::factory()->count(25)->create(['role' => 'user']);

        $response = $this->get(route('admin.user'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
        $response->assertViewHas('users');

        $users = $response->viewData('users');
        $this->assertEquals(20, $users->perPage());
        $this->assertEquals(25, $users->total());

        // Verify current admin is not in the list
        foreach ($users as $user) {
            $this->assertNotEquals($this->admin->id, $user->id);
        }
    }

    /**
     * Test index pagination works correctly
     */
    public function test_index_pagination_works_correctly(): void
    {
        User::factory()->count(25)->create(['role' => 'user']);

        $response = $this->get(route('admin.user', ['page' => 2]));

        $response->assertStatus(200);
        $users = $response->viewData('users');
        $this->assertEquals(2, $users->currentPage());
        $this->assertEquals(5, $users->count());
    }

    /**
     * Test create displays user creation form
     */
    public function test_create_displays_user_creation_form(): void
    {
        $response = $this->get(route('admin.user.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.create');
    }

    /**
     * Test store creates multiple users with valid data
     */
    public function test_store_creates_multiple_users_with_valid_data(): void
    {
        $data = [
            'users' => [
                [
                    'name' => 'User One',
                    'email' => 'user1@example.com',
                    'password' => 'password123',
                    'role' => 'user',
                ],
                [
                    'name' => 'User Two',
                    'email' => 'user2@example.com',
                    'password' => 'password456',
                    'role' => 'admin',
                ],
            ],
        ];

        $response = $this->post(route('admin.user.store'), $data);

        $response->assertRedirect(route('admin.user'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'User One',
            'email' => 'user1@example.com',
            'role' => 'user',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'role' => 'admin',
        ]);
    }

    /**
     * Test store encrypts passwords
     */
    public function test_store_encrypts_passwords(): void
    {
        $data = [
            'users' => [
                [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'password' => 'plainpassword',
                    'role' => 'user',
                ],
            ],
        ];

        $this->post(route('admin.user.store'), $data);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotEquals('plainpassword', $user->password);
        $this->assertTrue(Hash::check('plainpassword', $user->password));
    }

    /**
     * Test store validates required users array
     */
    public function test_store_validates_required_users_array(): void
    {
        $response = $this->post(route('admin.user.store'), []);

        $response->assertSessionHasErrors('users');
    }

    /**
     * Test store validates users array has at least one user
     */
    public function test_store_validates_users_array_has_at_least_one_user(): void
    {
        $response = $this->post(route('admin.user.store'), ['users' => []]);

        $response->assertSessionHasErrors('users');
    }

    /**
     * Test store validates name is required
     */
    public function test_store_validates_name_is_required(): void
    {
        $data = [
            'users' => [
                [
                    'email' => 'test@example.com',
                    'password' => 'password123',
                    'role' => 'user',
                ],
            ],
        ];

        $response = $this->post(route('admin.user.store'), $data);

        $response->assertSessionHasErrors('users.0.name');
    }

    /**
     * Test store validates email is required and valid
     */
    public function test_store_validates_email_is_required_and_valid(): void
    {
        $data = [
            'users' => [
                [
                    'name' => 'Test User',
                    'email' => 'invalid-email',
                    'password' => 'password123',
                    'role' => 'user',
                ],
            ],
        ];

        $response = $this->post(route('admin.user.store'), $data);

        $response->assertSessionHasErrors('users.0.email');
    }

    /**
     * Test store validates email is unique
     */
    public function test_store_validates_email_is_unique(): void
    {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $data = [
            'users' => [
                [
                    'name' => 'Test User',
                    'email' => 'existing@example.com',
                    'password' => 'password123',
                    'role' => 'user',
                ],
            ],
        ];

        $response = $this->post(route('admin.user.store'), $data);

        $response->assertSessionHasErrors('users.0.email');
    }

    /**
     * Test store validates password is required and minimum 6 characters
     */
    public function test_store_validates_password_is_required_and_minimum_six_characters(): void
    {
        $data = [
            'users' => [
                [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'password' => 'short',
                    'role' => 'user',
                ],
            ],
        ];

        $response = $this->post(route('admin.user.store'), $data);

        $response->assertSessionHasErrors('users.0.password');
    }

    /**
     * Test store validates role is required and valid
     */
    public function test_store_validates_role_is_required_and_valid(): void
    {
        $data = [
            'users' => [
                [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'password' => 'password123',
                    'role' => 'superadmin',
                ],
            ],
        ];

        $response = $this->post(route('admin.user.store'), $data);

        $response->assertSessionHasErrors('users.0.role');
    }

    /**
     * Test store invalidates cache
     */
    public function test_store_invalidates_cache(): void
    {
        Cache::put('admin_users_all', 'cached_data');

        $data = [
            'users' => [
                [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'password' => 'password123',
                    'role' => 'user',
                ],
            ],
        ];

        $this->post(route('admin.user.store'), $data);

        $this->assertNull(Cache::get('admin_users_all'));
    }

    /**
     * Test edit displays form with user data
     */
    public function test_edit_displays_form_with_user_data(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->get(route('admin.user.edit', $user->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.edit');
        $response->assertViewHas('user');

        $viewUser = $response->viewData('user');
        $this->assertEquals($user->id, $viewUser->id);
    }

    /**
     * Test edit prevents admin from editing themselves
     */
    public function test_edit_prevents_admin_from_editing_themselves(): void
    {
        $response = $this->get(route('admin.user.edit', $this->admin->id));

        $response->assertStatus(403);
    }

    /**
     * Test edit returns 404 for non-existent user
     */
    public function test_edit_returns_404_for_non_existent_user(): void
    {
        $response = $this->get(route('admin.user.edit', 9999));

        $response->assertStatus(404);
    }

    /**
     * Test update modifies user with valid data
     */
    public function test_update_modifies_user_with_valid_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'role' => 'user',
        ]);

        $data = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'admin',
        ];

        $response = $this->put(route('admin.user.update', $user->id), $data);

        $response->assertRedirect(route('admin.user'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'admin',
        ]);
    }

    /**
     * Test update allows keeping same email
     */
    public function test_update_allows_keeping_same_email(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user',
        ]);

        $data = [
            'name' => 'Updated Name',
            'email' => 'test@example.com',
            'role' => 'user',
        ];

        $response = $this->put(route('admin.user.update', $user->id), $data);

        $response->assertRedirect(route('admin.user'));
        $response->assertSessionHas('success');
    }

    /**
     * Test update validates email uniqueness for other users
     */
    public function test_update_validates_email_uniqueness_for_other_users(): void
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $data = [
            'name' => 'Test User',
            'email' => 'user1@example.com',
            'role' => 'user',
        ];

        $response = $this->put(route('admin.user.update', $user2->id), $data);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test update changes password when provided
     */
    public function test_update_changes_password_when_provided(): void
    {
        $user = User::factory()->create(['password' => bcrypt('oldpassword')]);

        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user',
            'password' => 'newpassword',
        ];

        $this->put(route('admin.user.update', $user->id), $data);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword', $user->password));
    }

    /**
     * Test update preserves password when not provided
     */
    public function test_update_preserves_password_when_not_provided(): void
    {
        $user = User::factory()->create(['password' => bcrypt('originalpassword')]);

        $data = [
            'name' => 'Updated Name',
            'email' => 'test@example.com',
            'role' => 'user',
        ];

        $this->put(route('admin.user.update', $user->id), $data);

        $user->refresh();
        $this->assertTrue(Hash::check('originalpassword', $user->password));
    }

    /**
     * Test update prevents admin from updating themselves
     */
    public function test_update_prevents_admin_from_updating_themselves(): void
    {
        $data = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'user',
        ];

        $response = $this->put(route('admin.user.update', $this->admin->id), $data);

        $response->assertStatus(403);
    }

    /**
     * Test update validates required fields
     */
    public function test_update_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->put(route('admin.user.update', $user->id), []);

        $response->assertSessionHasErrors(['name', 'email', 'role']);
    }

    /**
     * Test update validates password minimum length when provided
     */
    public function test_update_validates_password_minimum_length_when_provided(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user',
            'password' => 'short',
        ];

        $response = $this->put(route('admin.user.update', $user->id), $data);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test update invalidates cache
     */
    public function test_update_invalidates_cache(): void
    {
        $user = User::factory()->create();
        Cache::put('admin_users_all', 'cached_data');

        $data = [
            'name' => 'Updated Name',
            'email' => 'test@example.com',
            'role' => 'user',
        ];

        $this->put(route('admin.user.update', $user->id), $data);

        $this->assertNull(Cache::get('admin_users_all'));
    }

    /**
     * Test destroy deletes multiple users
     */
    public function test_destroy_deletes_multiple_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $response = $this->delete(route('admin.user.destroy.multiple'), [
            'ids' => [$user1->id, $user2->id],
        ]);

        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('users', ['id' => $user1->id]);
        $this->assertDatabaseMissing('users', ['id' => $user2->id]);
        $this->assertDatabaseHas('users', ['id' => $user3->id]);
    }

    /**
     * Test destroy normalizes single ID to array
     */
    public function test_destroy_normalizes_single_id_to_array(): void
    {
        $user = User::factory()->create();

        $response = $this->delete(route('admin.user.destroy.multiple'), [
            'ids' => $user->id,
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /**
     * Test destroy excludes admin's own ID
     */
    public function test_destroy_excludes_admin_own_id(): void
    {
        $user = User::factory()->create();

        $response = $this->delete(route('admin.user.destroy.multiple'), [
            'ids' => [$user->id, $this->admin->id],
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    /**
     * Test destroy blocks deletion of users with active orders
     */
    public function test_destroy_blocks_deletion_of_users_with_active_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->delete(route('admin.user.destroy.multiple'), [
            'ids' => [$user->id],
        ]);

        $response->assertJson([
            'success' => false,
            'blocked' => [$user->email],
        ]);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    /**
     * Test destroy allows deletion of users with completed orders
     */
    public function test_destroy_allows_deletion_of_users_with_completed_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
        ]);

        $response = $this->delete(route('admin.user.destroy.multiple'), [
            'ids' => [$user->id],
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /**
     * Test destroy allows deletion of users with cancelled orders
     */
    public function test_destroy_allows_deletion_of_users_with_cancelled_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'cancelled',
        ]);

        $response = $this->delete(route('admin.user.destroy.multiple'), [
            'ids' => [$user->id],
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /**
     * Test destroy handles mixed deletable and blocked users
     */
    public function test_destroy_handles_mixed_deletable_and_blocked_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Order::factory()->create([
            'user_id' => $user2->id,
            'status' => 'processing',
        ]);

        $response = $this->delete(route('admin.user.destroy.multiple'), [
            'ids' => [$user1->id, $user2->id],
        ]);

        $response->assertJson([
            'success' => false,
            'blocked' => [$user2->email],
        ]);
        $this->assertDatabaseMissing('users', ['id' => $user1->id]);
        $this->assertDatabaseHas('users', ['id' => $user2->id]);
    }

    /**
     * Test destroy invalidates cache
     */
    public function test_destroy_invalidates_cache(): void
    {
        $user = User::factory()->create();
        Cache::put('admin_users_all', 'cached_data');

        $this->delete(route('admin.user.destroy.multiple'), [
            'ids' => [$user->id],
        ]);

        $this->assertNull(Cache::get('admin_users_all'));
    }

    /**
     * Test destroySingle deletes specific user
     */
    public function test_destroy_single_deletes_specific_user(): void
    {
        $user = User::factory()->create();

        $response = $this->delete(route('admin.user.destroy', $user->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /**
     * Test destroySingle prevents admin from deleting themselves
     */
    public function test_destroy_single_prevents_admin_from_deleting_themselves(): void
    {
        $response = $this->delete(route('admin.user.destroy', $this->admin->id));

        $response->assertStatus(403);
    }

    /**
     * Test destroySingle blocks deletion of users with active orders
     */
    public function test_destroy_single_blocks_deletion_of_users_with_active_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'shipped',
        ]);

        $response = $this->delete(route('admin.user.destroy', $user->id));

        $response->assertJson(['success' => false]);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    /**
     * Test destroySingle returns JSON for AJAX requests
     */
    public function test_destroy_single_returns_json_for_ajax_requests(): void
    {
        $user = User::factory()->create();

        $response = $this->delete(
            route('admin.user.destroy', $user->id),
            [],
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertJson(['success' => true]);
    }

    /**
     * Test destroySingle returns 404 for non-existent user
     */
    public function test_destroy_single_returns_404_for_non_existent_user(): void
    {
        $response = $this->delete(route('admin.user.destroy', 9999));

        $response->assertStatus(404);
    }

    /**
     * Test destroySingle invalidates cache
     */
    public function test_destroy_single_invalidates_cache(): void
    {
        $user = User::factory()->create();
        Cache::put('admin_users_all', 'cached_data');

        $this->delete(route('admin.user.destroy', $user->id));

        $this->assertNull(Cache::get('admin_users_all'));
    }

    /**
     * Test show displays user details with orders and cart
     */
    public function test_show_displays_user_details_with_orders_and_cart(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(3)->create(['user_id' => $user->id]);
        Cart::factory()->count(2)->create(['user_id' => $user->id]);

        $response = $this->get(route('admin.user.show', $user->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.show');
        $response->assertViewHas('user');
        $response->assertViewHas('orders');
        $response->assertViewHas('carts');

        $orders = $response->viewData('orders');
        $carts = $response->viewData('carts');
        $this->assertEquals(3, $orders->count());
        $this->assertEquals(2, $carts->count());
    }

    /**
     * Test show eager loads product relationship for cart items
     */
    public function test_show_eager_loads_product_relationship_for_cart_items(): void
    {
        $user = User::factory()->create();
        Cart::factory()->count(2)->create(['user_id' => $user->id]);

        $response = $this->get(route('admin.user.show', $user->id));

        $carts = $response->viewData('carts');
        $this->assertTrue($carts->first()->relationLoaded('product'));
    }

    /**
     * Test show returns 404 for non-existent user
     */
    public function test_show_returns_404_for_non_existent_user(): void
    {
        $response = $this->get(route('admin.user.show', 9999));

        $response->assertStatus(404);
    }

    /**
     * Test search finds users by name
     */
    public function test_search_finds_users_by_name(): void
    {
        User::factory()->create(['name' => 'John Doe']);
        User::factory()->create(['name' => 'Jane Smith']);

        $response = $this->get(route('admin.user.search', ['query' => 'John']));

        $response->assertStatus(200);
        $html = $response->json('html');
        $this->assertStringContainsString('John Doe', $html);
        $this->assertStringNotContainsString('Jane Smith', $html);
    }

    /**
     * Test search finds users by email
     */
    public function test_search_finds_users_by_email(): void
    {
        User::factory()->create(['name' => 'User One', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'User Two', 'email' => 'jane@example.com']);

        $response = $this->get(route('admin.user.search', ['query' => 'john@']));

        $response->assertStatus(200);
        $html = $response->json('html');
        $this->assertStringContainsString('john@example.com', $html);
        $this->assertStringNotContainsString('jane@example.com', $html);
    }

    /**
     * Test search excludes current admin
     */
    public function test_search_excludes_current_admin(): void
    {
        $this->admin->update(['name' => 'Admin User']);
        User::factory()->create(['name' => 'Regular User']);

        $response = $this->get(route('admin.user.search', ['query' => 'User']));

        $html = $response->json('html');
        $this->assertStringContainsString('Regular User', $html);
        $this->assertStringNotContainsString('Admin User', $html);
    }

    /**
     * Test search returns HTML table rows
     */
    public function test_search_returns_html_table_rows(): void
    {
        User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);

        $response = $this->get(route('admin.user.search', ['query' => 'Test']));

        $response->assertStatus(200);
        $html = $response->json('html');
        $this->assertStringContainsString('<tr>', $html);
        $this->assertStringContainsString('Test User', $html);
        $this->assertStringContainsString('test@example.com', $html);
    }

    /**
     * Test search returns no results message when no users found
     */
    public function test_search_returns_no_results_message_when_no_users_found(): void
    {
        $response = $this->get(route('admin.user.search', ['query' => 'NonExistentUser']));

        $response->assertStatus(200);
        $html = $response->json('html');
        $this->assertStringContainsString('No users found', $html);
    }

    /**
     * Test search handles empty query
     */
    public function test_search_handles_empty_query(): void
    {
        User::factory()->count(5)->create();

        $response = $this->get(route('admin.user.search', ['query' => '']));

        $response->assertStatus(200);
        $html = $response->json('html');
        $this->assertStringContainsString('<tr>', $html);
    }

    /**
     * Test search uses partial matching
     */
    public function test_search_uses_partial_matching(): void
    {
        User::factory()->create(['name' => 'Alexander']);

        $response = $this->get(route('admin.user.search', ['query' => 'Alex']));

        $html = $response->json('html');
        $this->assertStringContainsString('Alexander', $html);
    }

    /**
     * Test search is case insensitive
     */
    public function test_search_is_case_insensitive(): void
    {
        User::factory()->create(['name' => 'TestUser']);

        $response = $this->get(route('admin.user.search', ['query' => 'testuser']));

        $html = $response->json('html');
        $this->assertStringContainsString('TestUser', $html);
    }

    /**
     * Test requires authentication
     */
    public function test_requires_authentication(): void
    {
        Auth::logout();

        $response = $this->get(route('admin.user'));

        $response->assertRedirect(route('login'));
    }
}
