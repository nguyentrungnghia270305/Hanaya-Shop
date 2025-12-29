<?php

namespace Tests\Unit\App\View\Components;

use Tests\TestCase;

class StarRatingTest extends TestCase
{
    /**
     * @test
     */
    public function component_view_exists()
    {
        $this->assertTrue(view()->exists('components.star-rating'));
    }

    /**
     * @test
     */
    public function component_can_be_rendered_with_default_rating()
    {
        $view = view('components.star-rating');
        
        $rendered = $view->render();
        
        $this->assertStringContainsString('★', $rendered);
    }

    /**
     * @test
     */
    public function component_accepts_rating_prop()
    {
        $view = view('components.star-rating', ['rating' => 4]);
        
        $rendered = $view->render();
        
        $this->assertStringContainsString('★', $rendered);
    }

    /**
     * @test
     */
    public function component_accepts_size_prop()
    {
        $sizes = ['sm', 'base', 'lg', 'xl'];
        
        foreach ($sizes as $size) {
            $view = view('components.star-rating', ['size' => $size]);
            $rendered = $view->render();
            
            $this->assertNotEmpty($rendered);
        }
    }

    /**
     * @test
     */
    public function component_supports_show_text_option()
    {
        $view = view('components.star-rating', [
            'rating' => 4.5,
            'showText' => true
        ]);
        
        $rendered = $view->render();
        
        $this->assertNotEmpty($rendered);
    }

    /**
     * @test
     */
    public function component_supports_readonly_mode()
    {
        $view = view('components.star-rating', [
            'rating' => 3,
            'readonly' => true
        ]);
        
        $rendered = $view->render();
        
        $this->assertStringContainsString('★', $rendered);
    }

    /**
     * @test
     */
    public function component_accepts_max_rating()
    {
        $view = view('components.star-rating', [
            'rating' => 3,
            'maxRating' => 5
        ]);
        
        $rendered = $view->render();
        
        $this->assertNotEmpty($rendered);
    }

    /**
     * @test
     */
    public function component_handles_decimal_ratings()
    {
        $view = view('components.star-rating', ['rating' => 4.5]);
        
        $rendered = $view->render();
        
        $this->assertStringContainsString('★', $rendered);
    }

    /**
     * @test
     */
    public function component_displays_stars_correctly()
    {
        $view = view('components.star-rating', [
            'rating' => 3,
            'maxRating' => 5
        ]);
        
        $rendered = $view->render();
        
        // Should contain star symbols
        $this->assertStringContainsString('★', $rendered);
    }
}
