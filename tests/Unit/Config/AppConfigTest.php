<?php

namespace Tests\Unit\Config;

use Tests\TestCase;

class AppConfigTest extends TestCase
{
    /**
     * @test
     */
    public function app_name_is_configured()
    {
        $name = config('app.name');

        $this->assertNotEmpty($name);
        $this->assertIsString($name);
    }

    /**
     * @test
     */
    public function app_environment_is_configured()
    {
        $env = config('app.env');

        $this->assertNotEmpty($env);
        $this->assertContains($env, ['local', 'testing', 'production']);
    }

    /**
     * @test
     */
    public function app_debug_mode_is_boolean()
    {
        $debug = config('app.debug');

        $this->assertIsBool($debug);
    }

    /**
     * @test
     */
    public function app_url_is_configured()
    {
        $url = config('app.url');

        $this->assertNotEmpty($url);
        $this->assertIsString($url);
    }

    /**
     * @test
     */
    public function app_timezone_is_configured()
    {
        $timezone = config('app.timezone');

        $this->assertNotEmpty($timezone);
        $this->assertEquals('UTC', $timezone);
    }

    /**
     * @test
     */
    public function app_locale_is_configured()
    {
        $locale = config('app.locale');

        $this->assertNotEmpty($locale);
        $this->assertIsString($locale);
    }

    /**
     * @test
     */
    public function app_fallback_locale_is_configured()
    {
        $fallbackLocale = config('app.fallback_locale');

        $this->assertNotEmpty($fallbackLocale);
        $this->assertEquals('en', $fallbackLocale);
    }
}
