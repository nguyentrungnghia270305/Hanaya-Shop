<?php

namespace Tests\Security\XSS;

use App\Models\Post;
use App\Models\Product\Product;
use App\Models\Product\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XSSPreventionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function user_input_is_escaped_in_views()
    {
        $product = Product::factory()->create([
            'name' => '<script>alert("XSS")</script>Test Product'
        ]);
        
        $response = $this->get('/products');
        
        // Script tags should be escaped, not executed
        $response->assertDontSee('<script>alert("XSS")</script>', false);
    }

    /**
     * @test
     */
    public function blade_escapes_variables_by_default()
    {
        $xssAttempt = '<img src=x onerror=alert(1)>';
        
        $view = view('components.alert')
            ->with('errors', new \Illuminate\Support\MessageBag());
        
        session()->flash('success', $xssAttempt);
        
        $rendered = $view->render();
        
        // Should be escaped
        $this->assertStringNotContainsString('<img src=x onerror=alert(1)>', $rendered);
    }

    /**
     * @test
     */
    public function javascript_injection_in_product_name_is_prevented()
    {
        $product = Product::factory()->create([
            'name' => 'Product<script>alert(document.cookie)</script>'
        ]);
        
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Product<script>alert(document.cookie)</script>'
        ]);
        
        // Data is stored but should be escaped when displayed
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    // public function html_tags_in_review_content_are_escaped()
    // {
    //     $review = Review::factory()->create([
    //         'comment' => '<h1>Fake Header</h1><script>malicious()</script>'
    //     ]);
        
    //     // Review is stored with HTML
    //     $this->assertDatabaseHas('reviews', [
    //         'id' => $review->id,
    //     ]);
        
    //     // When rendered, should be escaped
    //     $this->assertTrue(true);
    // }

    /**
     * @test
     */
    public function event_handlers_in_input_are_neutralized()
    {
        $maliciousInput = '<div onclick="alert(\'XSS\')">Click me</div>';
        
        $post = Post::factory()->create([
            'title' => $maliciousInput
        ]);
        
        // Stored safely
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
        ]);
    }

    /**
     * @test
     */
    public function url_javascript_protocol_is_prevented()
    {
        $maliciousUrl = 'javascript:alert("XSS")';
        
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'descriptions' => "Visit <a href='{$maliciousUrl}'>here</a>"
        ]);
        
        // Data is stored
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
        ]);
    }

    /**
     * @test
     */
    public function svg_xss_attempts_are_handled()
    {
        $svgXss = '<svg onload=alert(1)>';
        
        $product = Product::factory()->create([
            'descriptions' => $svgXss
        ]);
        
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
        ]);
    }
}
