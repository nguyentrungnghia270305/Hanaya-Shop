<?php

namespace Tests\Unit\Config;

use Tests\TestCase;

class ServicesConfigTest extends TestCase
{
    /**
     * @test
     */
    public function services_config_exists()
    {
        $services = config('services');
        
        $this->assertIsArray($services);
    }

    /**
     * @test
     */
    public function mailgun_service_is_configured_if_present()
    {
        $mailgun = config('services.mailgun');
        
        if ($mailgun) {
            $this->assertIsArray($mailgun);
        }
    }

    /**
     * @test
     */
    public function postmark_service_is_configured_if_present()
    {
        $postmark = config('services.postmark');
        
        if ($postmark) {
            $this->assertIsArray($postmark);
        }
    }

    /**
     * @test
     */
    public function ses_service_is_configured_if_present()
    {
        $ses = config('services.ses');
        
        if ($ses) {
            $this->assertIsArray($ses);
        }
    }

    /**
     * @test
     */
    public function slack_service_is_configured_if_present()
    {
        $slack = config('services.slack');
        
        if ($slack) {
            $this->assertIsArray($slack);
        }
    }
}
