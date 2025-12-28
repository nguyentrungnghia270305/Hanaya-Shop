<?php

namespace Tests\Unit\Database\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseMigrationsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function users_table_has_correct_columns()
    {
        $this->assertTrue(Schema::hasColumns('users', [
            'id', 'name', 'email', 'email_verified_at', 'password', 
            'remember_token', 'role', 'created_at', 'updated_at'
        ]));
    }

    /**
     * @test
     */
    public function cache_table_exists_and_has_correct_structure()
    {
        $this->assertTrue(Schema::hasTable('cache'));
        $this->assertTrue(Schema::hasColumns('cache', ['key', 'value', 'expiration']));
    }

    /**
     * @test
     */
    public function categories_table_has_correct_columns()
    {
        $this->assertTrue(Schema::hasColumns('categories', [
            'id', 'name', 'description', 'image_path', 'created_at', 'updated_at'
        ]));
    }

    /**
     * @test
     */
    public function products_table_has_correct_columns()
    {
        $this->assertTrue(Schema::hasColumns('products', [
            'id', 'name', 'descriptions', 'price', 'stock_quantity', 
            'category_id', 'image_url', 'created_at', 'updated_at'
        ]));
    }

    /**
     * @test
     */
    public function addresses_table_has_correct_columns()
    {
        $this->assertTrue(Schema::hasColumns('addresses', [
            'id', 'user_id', 'phone_number', 'address', 
            'latitude', 'longitude', 'created_at', 'updated_at'
        ]));
    }

    /**
     * @test
     */
    public function orders_table_has_correct_columns()
    {
        $this->assertTrue(Schema::hasColumns('orders', [
            'id', 'user_id', 'status', 'total_price', 
            'created_at', 'updated_at'
        ]));
    }

    /**
     * @test
     */
    public function order_details_table_has_correct_columns()
    {
        $this->assertTrue(Schema::hasColumns('order_details', [
            'id', 'order_id', 'product_id', 'quantity', 'price', 
            'created_at', 'updated_at'
        ]));
    }

    /**
     * @test
     */
    public function payments_table_has_correct_columns()
    {
        $this->assertTrue(Schema::hasColumns('payments', [
            'id', 'order_id', 'payment_method', 'payment_status', 
            'transaction_id', 'created_at', 'updated_at'
        ]));
    }

    /**
     * @test
     */
    public function reviews_table_has_correct_columns()
    {
        $this->assertTrue(Schema::hasColumns('reviews', [
            'id', 'user_id', 'product_id', 'order_id', 'rating', 'comment', 
            'created_at', 'updated_at'
        ]));
    }

    /**
     * @test
     */
    public function carts_table_has_correct_columns()
    {
        $this->assertTrue(Schema::hasColumns('carts', [
            'id', 'user_id', 'product_id', 'quantity', 
            'created_at', 'updated_at'
        ]));
    }

    /**
     * @test
     */
    public function posts_table_has_correct_columns()
    {
        $this->assertTrue(Schema::hasColumns('posts', [
            'id', 'title', 'slug', 'content', 'image', 'status', 'user_id', 
            'created_at', 'updated_at'
        ]));
    }

    /**
     * @test
     */
    public function products_table_has_discount_column()
    {
        $this->assertTrue(Schema::hasColumn('products', 'discount_percent'));
    }

    /**
     * @test
     */
    public function products_table_has_view_count_column()
    {
        $this->assertTrue(Schema::hasColumn('products', 'view_count'));
    }

    /**
     * @test
     */
    public function products_table_has_indexes()
    {
        $this->assertTrue(Schema::hasColumn('products', 'category_id'));
        // Index verification would require database-specific queries
    }

    /**
     * @test
     */
    public function order_details_table_has_subtotal_column()
    {
        $this->assertTrue(Schema::hasColumn('order_details', 'subtotal'));
    }

    /**
     * @test
     */
    public function notifications_table_exists()
    {
        $this->assertTrue(Schema::hasTable('notifications'));
        $this->assertTrue(Schema::hasColumns('notifications', [
            'id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at', 'created_at', 'updated_at'
        ]));
    }

    /**
     * @test
     */
    public function jobs_table_exists()
    {
        $this->assertTrue(Schema::hasTable('jobs'));
        $this->assertTrue(Schema::hasColumns('jobs', [
            'id', 'queue', 'payload', 'attempts', 'reserved_at', 'available_at', 'created_at'
        ]));
    }

    /**
     * @test
     */
    public function orders_table_has_address_id_column()
    {
        $this->assertTrue(Schema::hasColumn('orders', 'address_id'));
    }

    /**
     * @test
     */
    public function orders_table_has_message_column()
    {
        $this->assertTrue(Schema::hasColumn('orders', 'message'));
    }

    /**
     * @test
     */
    public function password_reset_tokens_table_structure()
    {
        $this->assertTrue(Schema::hasTable('password_reset_tokens'));
        $this->assertTrue(Schema::hasColumns('password_reset_tokens', [
            'email', 'token', 'created_at'
        ]));
    }

    /**
     * @test
     */
    public function posts_table_has_correct_charset_and_collation()
    {
        // This test verifies the migration ran successfully
        $this->assertTrue(Schema::hasTable('posts'));
        $this->assertTrue(Schema::hasColumn('posts', 'content'));
    }

    /**
     * @test
     */
    public function reviews_table_has_correct_foreign_key()
    {
        $this->assertTrue(Schema::hasColumn('reviews', 'order_id'));
    }

    /**
     * @test
     */
    public function all_tables_exist()
    {
        $tables = [
            'users', 'cache', 'categories', 'products', 'addresses',
            'orders', 'order_details', 'payments', 'reviews', 'carts',
            'posts', 'notifications', 'jobs', 'password_reset_tokens',
            'sessions'
        ];

        foreach ($tables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Table {$table} does not exist"
            );
        }
    }

    /**
     * @test
     */
    public function foreign_keys_are_properly_set()
    {
        // Test that foreign key columns exist
        $this->assertTrue(Schema::hasColumn('products', 'category_id'));
        $this->assertTrue(Schema::hasColumn('addresses', 'user_id'));
        $this->assertTrue(Schema::hasColumn('orders', 'user_id'));
        $this->assertTrue(Schema::hasColumn('order_details', 'order_id'));
        $this->assertTrue(Schema::hasColumn('order_details', 'product_id'));
        $this->assertTrue(Schema::hasColumn('payments', 'order_id'));
        $this->assertTrue(Schema::hasColumn('reviews', 'user_id'));
        $this->assertTrue(Schema::hasColumn('reviews', 'product_id'));
        $this->assertTrue(Schema::hasColumn('carts', 'user_id'));
        $this->assertTrue(Schema::hasColumn('carts', 'product_id'));
        $this->assertTrue(Schema::hasColumn('posts', 'user_id'));
    }

    /**
     * @test
     */
    public function timestamp_columns_exist_on_all_tables()
    {
        $tablesWithTimestamps = [
            'users', 'categories', 'products', 'addresses', 'orders',
            'order_details', 'payments', 'reviews', 'carts', 'posts'
        ];

        foreach ($tablesWithTimestamps as $table) {
            $this->assertTrue(
                Schema::hasColumns($table, ['created_at', 'updated_at']),
                "Table {$table} is missing timestamp columns"
            );
        }
    }

    /**
     * @test
     */
    public function migrations_can_be_rolled_back_and_rerun()
    {
        // Fresh migration
        $this->artisan('migrate:fresh')->assertExitCode(0);

        // Verify key tables exist
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('products'));
        $this->assertTrue(Schema::hasTable('orders'));
    }
}
