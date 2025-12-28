<?php

namespace Tests\Unit\Database\Migrations;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TableColumnsTest extends TestCase
{
    /**
     * @test
     */
    public function users_table_has_required_columns()
    {
        $columns = ['id', 'name', 'email', 'password', 'role', 'created_at', 'updated_at'];
        
        foreach ($columns as $column) {
            $this->assertTrue(Schema::hasColumn('users', $column));
        }
    }

    /**
     * @test
     */
    public function products_table_has_required_columns()
    {
        $columns = ['id', 'name', 'price', 'category_id', 'stock_quantity', 'created_at', 'updated_at'];
        
        foreach ($columns as $column) {
            $this->assertTrue(Schema::hasColumn('products', $column));
        }
    }

    /**
     * @test
     */
    public function orders_table_has_required_columns()
    {
        $columns = ['id', 'user_id', 'total_price', 'status', 'created_at', 'updated_at'];
        
        foreach ($columns as $column) {
            $this->assertTrue(Schema::hasColumn('orders', $column));
        }
    }

    /**
     * @test
     */
    public function categories_table_has_required_columns()
    {
        $columns = ['id', 'name', 'created_at', 'updated_at'];
        
        foreach ($columns as $column) {
            $this->assertTrue(Schema::hasColumn('categories', $column));
        }
    }

    /**
     * @test
     */
    public function carts_table_has_required_columns()
    {
        $columns = ['id', 'product_id', 'quantity', 'created_at', 'updated_at'];
        
        foreach ($columns as $column) {
            $this->assertTrue(Schema::hasColumn('carts', $column));
        }
    }

    /**
     * @test
     */
    public function reviews_table_has_required_columns()
    {
        $columns = ['id', 'user_id', 'product_id', 'rating', 'created_at', 'updated_at'];
        
        foreach ($columns as $column) {
            $this->assertTrue(Schema::hasColumn('reviews', $column));
        }
    }
}
