<?php

namespace Tests\Unit\App\View\Components;

use App\View\Components\ProductCard;
use Tests\TestCase;

class ProductCardTest extends TestCase
{
    /**
     * @test
     */
    public function component_can_be_instantiated()
    {
        $component = new ProductCard;

        $this->assertInstanceOf(ProductCard::class, $component);
    }

    /**
     * @test
     */
    public function component_has_render_method()
    {
        $component = new ProductCard;

        $this->assertTrue(method_exists($component, 'render'));
    }

    /**
     * @test
     */
    public function component_renders_correct_view()
    {
        $component = new ProductCard;

        $view = $component->render();

        $this->assertEquals('components.product-card', $view->name());
    }

    /**
     * @test
     */
    public function component_can_be_rendered()
    {
        $component = new ProductCard;

        $view = $component->render();

        $this->assertNotNull($view);
    }

    /**
     * @test
     */
    public function component_view_exists()
    {
        $this->assertTrue(view()->exists('components.product-card'));
    }

    /**
     * @test
     */
    public function component_is_registered()
    {
        $this->assertTrue(class_exists(ProductCard::class));
    }
}
