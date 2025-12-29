<?php

namespace Tests\Unit\App\Exceptions;

use Exception;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Tests\TestCase;

class HandlerTest extends TestCase
{
    /**
     * @test
     */
    public function application_has_exception_handler()
    {
        $app = $this->app;
        
        $this->assertTrue($app->bound('Illuminate\Contracts\Debug\ExceptionHandler'));
    }

    /**
     * @test
     */
    public function exception_handler_can_be_resolved()
    {
        $handler = $this->app->make('Illuminate\Contracts\Debug\ExceptionHandler');
        
        $this->assertNotNull($handler);
        $this->assertInstanceOf('Illuminate\Contracts\Debug\ExceptionHandler', $handler);
    }

    /**
     * @test
     */
    public function exception_handler_has_report_method()
    {
        $handler = $this->app->make('Illuminate\Contracts\Debug\ExceptionHandler');
        
        $this->assertTrue(method_exists($handler, 'report'));
    }

    /**
     * @test
     */
    public function exception_handler_has_render_method()
    {
        $handler = $this->app->make('Illuminate\Contracts\Debug\ExceptionHandler');
        
        $this->assertTrue(method_exists($handler, 'render'));
    }

    /**
     * @test
     */
    public function exception_handler_can_render_exception_to_response()
    {
        $handler = $this->app->make('Illuminate\Contracts\Debug\ExceptionHandler');
        $request = Request::create('/test', 'GET');
        $exception = new Exception('Test exception');
        
        $response = $handler->render($request, $exception);
        
        $this->assertNotNull($response);
        $this->assertTrue(method_exists($response, 'getStatusCode'));
    }

    /**
     * @test
     */
    public function exception_handler_configuration_exists()
    {
        $appPath = base_path('bootstrap/app.php');
        
        $this->assertFileExists($appPath);
        
        $content = file_get_contents($appPath);
        $this->assertStringContainsString('withExceptions', $content);
    }
}
