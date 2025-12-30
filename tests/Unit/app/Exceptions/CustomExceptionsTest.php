<?php

namespace Tests\Unit\App\Exceptions;

use Exception;
use Tests\TestCase;

class CustomExceptionsTest extends TestCase
{
    /**
     * @test
     */
    public function exception_can_be_thrown_with_message()
    {
        $message = 'Custom error message';

        $exception = new Exception($message);

        $this->assertEquals($message, $exception->getMessage());
    }

    /**
     * @test
     */
    public function exception_can_be_thrown_with_code()
    {
        $code = 404;

        $exception = new Exception('Not found', $code);

        $this->assertEquals($code, $exception->getCode());
    }

    /**
     * @test
     */
    public function exception_has_file_and_line_information()
    {
        $exception = new Exception('Test');

        $this->assertNotEmpty($exception->getFile());
        $this->assertIsInt($exception->getLine());
        $this->assertGreaterThan(0, $exception->getLine());
    }

    /**
     * @test
     */
    public function exception_can_be_caught_and_handled()
    {
        $caught = false;

        try {
            throw new Exception('Test exception');
        } catch (Exception $e) {
            $caught = true;
        }

        $this->assertTrue($caught);
    }

    /**
     * @test
     */
    public function exception_has_trace_information()
    {
        $exception = new Exception('Test');

        $trace = $exception->getTrace();

        $this->assertIsArray($trace);
    }
}
