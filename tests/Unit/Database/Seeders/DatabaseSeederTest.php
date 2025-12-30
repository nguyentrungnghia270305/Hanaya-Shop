<?php

namespace Tests\Unit\Database\Seeders;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function database_seeder_runs_successfully()
    {
        $seeder = new DatabaseSeeder;

        $this->assertNull($seeder->run());
    }

    /**
     * @test
     */
    public function database_seeder_creates_users()
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThan(0, \App\Models\User::count());
    }

    /**
     * @test
     */
    public function database_seeder_creates_categories()
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThan(0, \App\Models\Product\Category::count());
    }

    /**
     * @test
     */
    public function database_seeder_creates_products()
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThan(0, \App\Models\Product\Product::count());
    }

    /**
     * @test
     */
    public function database_seeder_creates_posts()
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThan(0, \App\Models\Post::count());
    }
}
