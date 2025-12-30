<?php

namespace Tests\Unit\Database\Migrations;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ForeignKeyConstraintsTest extends TestCase
{
    /**
     * @test
     */
    public function products_table_has_category_foreign_key()
    {
        $this->assertTrue(Schema::hasColumn('products', 'category_id'));
    }

    /**
     * @test
     */
    public function orders_table_has_user_foreign_key()
    {
        $this->assertTrue(Schema::hasColumn('orders', 'user_id'));
    }

    /**
     * @test
     */
    public function orders_table_has_address_foreign_key()
    {
        $this->assertTrue(Schema::hasColumn('orders', 'address_id'));
    }

    /**
     * @test
     */
    public function reviews_table_has_user_foreign_key()
    {
        $this->assertTrue(Schema::hasColumn('reviews', 'user_id'));
    }

    /**
     * @test
     */
    public function reviews_table_has_product_foreign_key()
    {
        $this->assertTrue(Schema::hasColumn('reviews', 'product_id'));
    }

    /**
     * @test
     */
    public function carts_table_has_product_foreign_key()
    {
        $this->assertTrue(Schema::hasColumn('carts', 'product_id'));
    }

    /**
     * @test
     */
    public function payments_table_has_order_foreign_key()
    {
        $this->assertTrue(Schema::hasColumn('payments', 'order_id'));
    }

    /**
     * @test
     */
    public function order_details_table_has_order_foreign_key()
    {
        $this->assertTrue(Schema::hasColumn('order_details', 'order_id'));
    }

    /**
     * @test
     */
    public function order_details_table_has_product_foreign_key()
    {
        $this->assertTrue(Schema::hasColumn('order_details', 'product_id'));
    }

    /**
     * @test
     */
    public function addresses_table_has_user_foreign_key()
    {
        $this->assertTrue(Schema::hasColumn('addresses', 'user_id'));
    }
}
