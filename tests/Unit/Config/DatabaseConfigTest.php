<?php

namespace Tests\Unit\Config;

use Tests\TestCase;

class DatabaseConfigTest extends TestCase
{
    /**
     * @test
     */
    public function default_connection_is_configured()
    {
        $default = config('database.default');

        $this->assertNotEmpty($default);
        $this->assertIsString($default);
    }

    /**
     * @test
     */
    public function mysql_connection_is_configured()
    {
        $mysql = config('database.connections.mysql');

        $this->assertIsArray($mysql);
        $this->assertArrayHasKey('driver', $mysql);
        $this->assertEquals('mysql', $mysql['driver']);
    }

    /**
     * @test
     */
    public function connections_are_configured()
    {
        $connections = config('database.connections');

        $this->assertIsArray($connections);
        $this->assertNotEmpty($connections);
    }

    /**
     * @test
     */
    public function database_name_is_configured()
    {
        $database = config('database.connections.mysql.database');

        $this->assertNotEmpty($database);
    }

    /**
     * @test
     */
    public function charset_is_configured()
    {
        $charset = config('database.connections.mysql.charset');

        $this->assertEquals('utf8mb4', $charset);
    }

    /**
     * @test
     */
    public function collation_is_configured()
    {
        $collation = config('database.connections.mysql.collation');

        $this->assertEquals('utf8mb4_unicode_ci', $collation);
    }
}
