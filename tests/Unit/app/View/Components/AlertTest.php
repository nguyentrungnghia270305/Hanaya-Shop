<?php

namespace Tests\Unit\App\View\Components;

use App\View\Components\Alert;
use Tests\TestCase;

class AlertTest extends TestCase
{
    /**
     * @test
     */
    public function component_can_be_instantiated()
    {
        $component = new Alert;

        $this->assertInstanceOf(Alert::class, $component);
    }

    /**
     * @test
     */
    public function component_has_render_method()
    {
        $component = new Alert;

        $this->assertTrue(method_exists($component, 'render'));
    }

    /**
     * @test
     */
    public function component_renders_correct_view()
    {
        $component = new Alert;

        $view = $component->render();

        $this->assertEquals('components.alert', $view->name());
    }

    /**
     * @test
     */
    public function component_can_be_rendered()
    {
        $component = new Alert;

        $view = $component->render();

        $this->assertNotNull($view);
    }

    /**
     * @test
     */
    public function component_view_exists()
    {
        $this->assertTrue(view()->exists('components.alert'));
    }

    /**
     * @test
     */
    public function component_is_registered()
    {
        $this->assertTrue(class_exists(Alert::class));
    }
}
