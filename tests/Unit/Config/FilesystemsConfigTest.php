<?php

namespace Tests\Unit\Config;

use Tests\TestCase;

class FilesystemsConfigTest extends TestCase
{
    /**
     * @test
     */
    public function default_disk_is_configured()
    {
        $default = config('filesystems.default');
        
        $this->assertNotEmpty($default);
        $this->assertIsString($default);
    }

    /**
     * @test
     */
    public function disks_are_configured()
    {
        $disks = config('filesystems.disks');
        
        $this->assertIsArray($disks);
        $this->assertNotEmpty($disks);
    }

    /**
     * @test
     */
    public function public_disk_is_configured()
    {
        $publicDisk = config('filesystems.disks.public');
        
        $this->assertIsArray($publicDisk);
        $this->assertArrayHasKey('driver', $publicDisk);
        $this->assertEquals('local', $publicDisk['driver']);
    }

    /**
     * @test
     */
    public function local_disk_is_configured()
    {
        $localDisk = config('filesystems.disks.local');
        
        $this->assertIsArray($localDisk);
        $this->assertArrayHasKey('driver', $localDisk);
    }

    /**
     * @test
     */
    public function public_disk_has_visibility()
    {
        $visibility = config('filesystems.disks.public.visibility');
        
        $this->assertEquals('public', $visibility);
    }
}
