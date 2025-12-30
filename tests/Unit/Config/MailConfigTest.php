<?php

namespace Tests\Unit\Config;

use Tests\TestCase;

class MailConfigTest extends TestCase
{
    /**
     * @test
     */
    public function default_mailer_is_configured()
    {
        $default = config('mail.default');

        $this->assertNotEmpty($default);
        $this->assertIsString($default);
    }

    /**
     * @test
     */
    public function mailers_are_configured()
    {
        $mailers = config('mail.mailers');

        $this->assertIsArray($mailers);
        $this->assertNotEmpty($mailers);
    }

    /**
     * @test
     */
    public function smtp_mailer_is_configured()
    {
        $smtp = config('mail.mailers.smtp');

        $this->assertIsArray($smtp);
        $this->assertArrayHasKey('transport', $smtp);
    }

    /**
     * @test
     */
    public function from_address_is_configured()
    {
        $from = config('mail.from');

        $this->assertIsArray($from);
        $this->assertArrayHasKey('address', $from);
        $this->assertArrayHasKey('name', $from);
    }

    /**
     * @test
     */
    public function from_address_is_valid_email()
    {
        $fromAddress = config('mail.from.address');

        $this->assertNotEmpty($fromAddress);
        $this->assertMatchesRegularExpression('/^[^@]+@[^@]+\.[^@]+$/', $fromAddress);
    }
}
