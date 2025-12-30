<?php

namespace Tests\Security\SQL;

use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SQLInjectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function sql_injection_in_login_is_prevented()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Attempt SQL injection in email field
        $response = $this->post(route('login'), [
            'email' => "admin' OR '1'='1",
            'password' => 'password',
        ]);

        // Should not authenticate with SQL injection attempt
        $this->assertGuest();
    }

    /**
     * @test
     */
    public function sql_injection_in_search_is_prevented()
    {
        Product::factory()->create(['name' => 'Test Product']);

        $maliciousQuery = "'; DROP TABLE products; --";

        // Eloquent query with malicious input
        $products = Product::where('name', 'LIKE', "%{$maliciousQuery}%")->get();

        // Query should run safely without injection
        $this->assertCount(0, $products);
        // Products table should still exist
        $this->assertDatabaseCount('products', 1);
    }

    /**
     * @test
     */
    public function eloquent_uses_parameter_binding()
    {
        $maliciousInput = "1' OR '1'='1";

        // Eloquent should safely handle this
        $users = User::where('id', $maliciousInput)->get();

        // Should return no results, not all users
        $this->assertEquals(0, $users->count());
    }

    /**
     * @test
     */
    public function prepared_statements_prevent_injection_in_raw_queries()
    {
        User::factory()->create(['email' => 'real@example.com']);

        $maliciousEmail = "admin' OR '1'='1";

        // Using parameter binding
        $users = DB::select('SELECT * FROM users WHERE email = ?', [$maliciousEmail]);

        // Should return no results
        $this->assertEmpty($users);
    }

    /**
     * @test
     */
    public function special_characters_are_safely_handled()
    {
        $product = Product::factory()->create([
            'name' => "Test'; DROP TABLE products; --",
        ]);

        // Product should be created safely
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => "Test'; DROP TABLE products; --",
        ]);

        // Table should still exist
        $this->assertDatabaseCount('products', 1);
    }

    /**
     * @test
     */
    public function where_in_prevents_sql_injection()
    {
        User::factory()->count(3)->create();

        // Malicious input in whereIn
        $ids = ["1' OR '1'='1"];

        $users = User::whereIn('id', $ids)->get();

        // Should return no users
        $this->assertEquals(0, $users->count());
    }
}
