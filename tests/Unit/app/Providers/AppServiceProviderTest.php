<?php

namespace Tests\Unit\App\Providers;

use App\Providers\AppServiceProvider;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    /**
     * @test
     */
    public function provider_is_registered_in_application()
    {
        $providers = $this->app->getLoadedProviders();

        $this->assertArrayHasKey(AppServiceProvider::class, $providers);
    }

    /**
     * @test
     */
    public function provider_has_register_method()
    {
        $provider = new AppServiceProvider($this->app);

        $this->assertTrue(method_exists($provider, 'register'));
    }

    /**
     * @test
     */
    public function provider_has_boot_method()
    {
        $provider = new AppServiceProvider($this->app);

        $this->assertTrue(method_exists($provider, 'boot'));
    }

    /**
     * @test
     */
    public function provider_can_be_instantiated()
    {
        $provider = new AppServiceProvider($this->app);

        $this->assertInstanceOf(AppServiceProvider::class, $provider);
    }

    /**
     * @test
     */
    public function provider_configuration_exists()
    {
        $providersPath = base_path('bootstrap/providers.php');

        $this->assertFileExists($providersPath);

        $providers = require $providersPath;
        $this->assertContains(AppServiceProvider::class, $providers);
    }
}
