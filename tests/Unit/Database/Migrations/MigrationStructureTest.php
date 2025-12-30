<?php

namespace Tests\Unit\Database\Migrations;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationStructureTest extends TestCase
{
    /**
     * @test
     */
    public function users_table_exists()
    {
        $this->assertTrue(Schema::hasTable('users'));
    }

    /**
     * @test
     */
    public function products_table_exists()
    {
        $this->assertTrue(Schema::hasTable('products'));
    }

    /**
     * @test
     */
    public function orders_table_exists()
    {
        $this->assertTrue(Schema::hasTable('orders'));
    }

    /**
     * @test
     */
    public function categories_table_exists()
    {
        $this->assertTrue(Schema::hasTable('categories'));
    }

    /**
     * @test
     */
    public function carts_table_exists()
    {
        $this->assertTrue(Schema::hasTable('carts'));
    }

    /**
     * @test
     */
    public function reviews_table_exists()
    {
        $this->assertTrue(Schema::hasTable('reviews'));
    }

    /**
     * @test
     */
    public function payments_table_exists()
    {
        $this->assertTrue(Schema::hasTable('payments'));
    }

    /**
     * @test
     */
    public function addresses_table_exists()
    {
        $this->assertTrue(Schema::hasTable('addresses'));
    }

    /**
     * @test
     */
    public function posts_table_exists()
    {
        $this->assertTrue(Schema::hasTable('posts'));
    }

    /**
     * @test
     */
    public function order_details_table_exists()
    {
        $this->assertTrue(Schema::hasTable('order_details'));
    }
}
