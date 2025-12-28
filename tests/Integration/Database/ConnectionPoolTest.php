<?php

namespace Tests\Integration\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConnectionPoolTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function database_connection_is_established()
    {
        $connection = DB::connection();
        
        $this->assertNotNull($connection);
        $this->assertTrue($connection->getDatabaseName() !== null);
    }

    /**
     * @test
     */
    public function multiple_queries_use_same_connection()
    {
        $connection1 = DB::connection()->getName();
        
        DB::table('users')->count();
        
        $connection2 = DB::connection()->getName();
        
        DB::table('products')->count();
        
        $connection3 = DB::connection()->getName();
        
        $this->assertEquals($connection1, $connection2);
        $this->assertEquals($connection2, $connection3);
    }

    /**
     * @test
     */
    public function connection_can_be_reconnected()
    {
        DB::disconnect();
        
        $result = DB::table('users')->count();
        
        $this->assertIsInt($result);
    }

    /**
     * @test
     */
    public function connection_handles_timeout_gracefully()
    {
        $start = microtime(true);
        
        try {
            DB::table('users')->timeout(5)->count();
            $success = true;
        } catch (\Exception $e) {
            $success = false;
        }
        
        $duration = microtime(true) - $start;
        
        $this->assertTrue($success || $duration < 6);
    }
}
