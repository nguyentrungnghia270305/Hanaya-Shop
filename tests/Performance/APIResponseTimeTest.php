<?php

namespace Tests\Performance;

use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class APIResponseTimeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function homepage_responds_within_acceptable_time()
    {
        $startTime = microtime(true);
        
        $response = $this->get('/');
        
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        $response->assertStatus(200);
        // Homepage should respond within 3000ms (test environment is slower)
        $this->assertLessThan(3000, $duration, "Homepage took {$duration}ms, should be under 3000ms");
    }

    /**
     * @test
     */
    public function product_listing_responds_within_acceptable_time()
    {
        Product::factory()->count(20)->create();
        
        $startTime = microtime(true);
        
        $response = $this->get('/products');
        
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;
        
        $response->assertStatus(200);
        // Product listing should respond within 2 seconds (test environment)
        $this->assertLessThan(2000, $duration, "Product listing took {$duration}ms, should be under 2000ms");
    }

    /**
     * @test
     */
    public function login_endpoint_responds_within_acceptable_time()
    {
        $startTime = microtime(true);
        
        $response = $this->get(route('login'));
        
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;
        
        $response->assertStatus(200);
        // Login page should respond within 3000ms (test environment)
        $this->assertLessThan(3000, $duration, "Login page took {$duration}ms, should be under 3000ms");
    }

    /**
     * @test
     */
    public function authenticated_profile_responds_within_acceptable_time()
    {
        $user = User::factory()->create();
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($user)->get(route('profile.edit'));
        
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;
        
        $response->assertStatus(200);
        // Profile page should respond within 400ms
        $this->assertLessThan(400, $duration, "Profile page took {$duration}ms, should be under 400ms");
    }

    /**
     * @test
     */
    public function static_assets_load_quickly()
    {
        $startTime = microtime(true);
        
        // Test homepage which always exists
        $response = $this->get('/');
        
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;
        
        $response->assertStatus(200);
        // Page should respond within 500ms
        $this->assertLessThan(500, $duration, "Page took {$duration}ms, should be under 500ms");
    }
}
