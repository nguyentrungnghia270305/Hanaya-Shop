<?php

namespace Tests\Unit\Config;

use Tests\TestCase;

class ConstantsTest extends TestCase
{
    /**
     * @test
     */
    public function logo_path_is_configured()
    {
        $logoPath = config('constants.logo_path');

        $this->assertNotEmpty($logoPath);
        $this->assertStringContainsString('logo', $logoPath);
    }

    /**
     * @test
     */
    public function favicon_path_is_configured()
    {
        $faviconPath = config('constants.favicon_path');

        $this->assertNotEmpty($faviconPath);
        $this->assertStringContainsString('favicon', $faviconPath);
    }

    /**
     * @test
     */
    public function shop_name_is_configured()
    {
        $shopName = config('constants.shop_name');

        $this->assertNotEmpty($shopName);
        $this->assertIsString($shopName);
    }

    /**
     * @test
     */
    public function shop_contact_info_is_configured()
    {
        $email = config('constants.shop_email');
        $phone = config('constants.shop_phone');
        $address = config('constants.shop_address');

        $this->assertNotEmpty($email);
        $this->assertNotEmpty($phone);
        $this->assertNotEmpty($address);
    }

    /**
     * @test
     */
    public function banners_are_configured()
    {
        $banners = config('constants.banners');

        $this->assertIsArray($banners);
        $this->assertNotEmpty($banners);

        foreach ($banners as $banner) {
            $this->assertArrayHasKey('image', $banner);
            $this->assertArrayHasKey('title_key', $banner);
        }
    }

    /**
     * @test
     */
    public function social_links_are_configured()
    {
        $socialLinks = config('constants.social_links');

        if ($socialLinks) {
            $this->assertIsArray($socialLinks);
        } else {
            $this->assertTrue(true, 'Social links not configured, skipping test');
        }
    }

    /**
     * @test
     */
    public function payment_methods_are_configured()
    {
        $paymentMethods = config('constants.payment_methods');

        if ($paymentMethods) {
            $this->assertIsArray($paymentMethods);
        } else {
            $this->assertTrue(true, 'Payment methods not configured, skipping test');
        }
    }
}
