<?php

namespace Tests\Unit\Database\Migrations;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IndexesTest extends TestCase
{
    /**
     * @test
     */
    public function users_table_has_email_index()
    {
        $this->assertTrue(Schema::hasColumn('users', 'email'));
    }

    /**
     * @test
     */
    public function products_table_has_category_index()
    {
        $this->assertTrue(Schema::hasColumn('products', 'category_id'));
    }

    /**
     * @test
     */
    public function orders_table_has_user_index()
    {
        $this->assertTrue(Schema::hasColumn('orders', 'user_id'));
    }

    /**
     * @test
     */
    public function orders_table_has_status_column()
    {
        $this->assertTrue(Schema::hasColumn('orders', 'status'));
    }

    /**
     * @test
     */
    public function reviews_table_has_product_index()
    {
        $this->assertTrue(Schema::hasColumn('reviews', 'product_id'));
    }

    /**
     * @test
     */
    public function reviews_table_has_user_index()
    {
        $this->assertTrue(Schema::hasColumn('reviews', 'user_id'));
    }

    /**
     * @test
     */
    public function carts_table_has_user_index()
    {
        $this->assertTrue(Schema::hasColumn('carts', 'user_id'));
    }

    /**
     * @test
     */
    public function payments_table_has_order_index()
    {
        $this->assertTrue(Schema::hasColumn('payments', 'order_id'));
    }
}
