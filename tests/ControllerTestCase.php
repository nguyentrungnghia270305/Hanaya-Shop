<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Base Test Case for Controller Tests
 * 
 * This class provides common setup for all controller tests,
 * including database refresh and session handling.
 * 
 * CSRF protection is automatically disabled via DisableCsrfForTesting middleware
 * when APP_ENV=testing.
 */
abstract class ControllerTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure we're in testing environment
        $this->app['env'] = 'testing';
    }
}
