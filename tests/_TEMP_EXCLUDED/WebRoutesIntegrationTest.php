<?php

namespace Tests\Feature\Routes;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class WebRoutesIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function chatbot_route_exists()
    {
        $this->assertTrue(Route::has('chatbot.chat'));
    }

    /** @test */
    public function locale_switch_route_exists()
    {
        $this->assertTrue(Route::has('locale.set'));
    }

    /** @test */
    public function chatbot_endpoint_is_accessible()
    {
        $response = $this->post(route('chatbot.chat'), [
            'message' => 'test message',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['response']);
    }

    /** @test */
    public function locale_can_be_switched()
    {
        $response = $this->get(route('locale.set', ['locale' => 'vi']));

        $response->assertRedirect();
        $this->assertEquals('vi', session('locale'));
    }

    /** @test */
    public function locale_switch_validates_locale()
    {
        $response = $this->get(route('locale.set', ['locale' => 'invalid']));

        // The route pattern only allows 2 letter locales, so 'invalid' would be a 404
        $response->assertNotFound();
    }

    /** @test */
    public function locale_switch_accepts_en()
    {
        $response = $this->get(route('locale.set', ['locale' => 'en']));
        $response->assertRedirect();
    }

    /** @test */
    public function locale_switch_accepts_vi()
    {
        $response = $this->get(route('locale.set', ['locale' => 'vi']));
        $response->assertRedirect();
    }

    /** @test */
    public function locale_switch_accepts_ja()
    {
        $response = $this->get(route('locale.set', ['locale' => 'ja']));
        $response->assertRedirect();
    }

    /** @test */
    public function web_routes_include_user_routes()
    {
        $this->assertTrue(Route::has('user.dashboard'));
        $this->assertTrue(Route::has('user.products.index'));
    }

    /** @test */
    public function web_routes_include_admin_routes()
    {
        $this->assertTrue(Route::has('admin.dashboard'));
        $this->assertTrue(Route::has('admin.product'));
    }

    /** @test */
    public function web_routes_include_auth_routes()
    {
        $this->assertTrue(Route::has('login'));
        $this->assertTrue(Route::has('register'));
    }
}
