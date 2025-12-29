<?php

namespace Tests\Unit\App\Console\Commands;

use App\Models\Order\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class TestEmailConfigCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test command signature is correctly defined
     */
    public function test_command_signature_is_defined(): void
    {
        $commands = Artisan::all();
        
        $this->assertArrayHasKey('test:email-config', $commands);
    }

    /**
     * Test command description is set
     */
    public function test_command_has_description(): void
    {
        $command = Artisan::all()['test:email-config'];
        
        $this->assertNotEmpty($command->getDescription());
        $this->assertStringContainsString('email', strtolower($command->getDescription()));
    }

    /**
     * Test command displays current app URL
     */
    public function test_command_displays_app_url(): void
    {
        Config::set('app.url', 'http://hanayashop.test');
        
        $this->artisan('test:email-config')
            ->expectsOutput('Testing email configuration...')
            ->expectsOutput('Current APP_URL: http://hanayashop.test')
            ->assertExitCode(0);
    }

    /**
     * Test command displays mail configuration correctly
     */
    public function test_command_displays_mail_configuration(): void
    {
        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', 'smtp.gmail.com');
        Config::set('mail.from.address', 'noreply@hanayashop.com');
        Config::set('mail.from.name', 'Hanaya Shop');

        $this->artisan('test:email-config')
            ->expectsOutput('Mail configuration:')
            ->expectsOutput('- MAIL_MAILER: smtp')
            ->expectsOutput('- MAIL_HOST: smtp.gmail.com')
            ->expectsOutput('- MAIL_FROM_ADDRESS: noreply@hanayashop.com')
            ->expectsOutput('- MAIL_FROM_NAME: Hanaya Shop')
            ->assertExitCode(0);
    }

    /**
     * Test command displays generated URL for admin orders
     */
    public function test_command_displays_generated_admin_order_url(): void
    {
        Config::set('app.url', 'https://hanayashop.com');
        
        $this->artisan('test:email-config')
            ->expectsOutput('Generated URL for admin orders: https://hanayashop.com/admin/orders/1')
            ->assertExitCode(0);
    }

    /**
     * Test command counts admin users correctly
     */
    public function test_command_counts_admin_users(): void
    {
        // Create admin users
        User::factory()->count(3)->create(['role' => 'admin']);
        User::factory()->count(2)->create(['role' => 'user']);
        
        $this->artisan('test:email-config')
            ->expectsOutput('Found 3 admin users')
            ->assertExitCode(0);
    }

    /**
     * Test command with no admin users
     */
    public function test_command_with_no_admin_users(): void
    {
        User::factory()->count(2)->create(['role' => 'user']);
        
        $this->artisan('test:email-config')
            ->expectsOutput('Found 0 admin users')
            ->assertExitCode(0);
    }

    /**
     * Test command finds sample order when orders exist
     */
    public function test_command_finds_sample_order_when_orders_exist(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        
        $this->artisan('test:email-config')
            ->expectsOutputToContain('Found sample order with ID: '.$order->id)
            ->assertExitCode(0);
    }

    /**
     * Test command shows warning when no orders exist
     */
    public function test_command_warns_when_no_orders_exist(): void
    {
        $this->artisan('test:email-config')
            ->expectsOutput('No orders found in database')
            ->assertExitCode(0);
    }

    /**
     * Test command returns success exit code
     */
    public function test_command_returns_success_exit_code(): void
    {
        $exitCode = $this->artisan('test:email-config')->run();
        
        $this->assertEquals(0, $exitCode);
    }

    /**
     * Test command works with null mail configuration
     */
    public function test_command_handles_null_mail_configuration(): void
    {
        Config::set('mail.mailers.smtp.host', null);
        Config::set('mail.from.address', null);
        
        $this->artisan('test:email-config')
            ->assertExitCode(0);
    }

    /**
     * Test command with multiple orders
     */
    public function test_command_with_multiple_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->count(5)->create(['user_id' => $user->id]);
        
        $this->artisan('test:email-config')
            ->expectsOutputToContain('Found sample order with ID:')
            ->assertExitCode(0);
    }

    /**
     * Test command displays all required information sections
     */
    public function test_command_displays_all_information_sections(): void
    {
        Config::set('app.url', 'http://test.com');
        User::factory()->create(['role' => 'admin']);
        
        $this->artisan('test:email-config')
            ->expectsOutput('Testing email configuration...')
            ->expectsOutputToContain('Current APP_URL:')
            ->expectsOutput('Mail configuration:')
            ->expectsOutputToContain('Generated URL for admin orders:')
            ->expectsOutputToContain('Found')
            ->assertExitCode(0);
    }

    /**
     * Test command with localhost app URL
     */
    public function test_command_with_localhost_app_url(): void
    {
        Config::set('app.url', 'http://localhost:8000');
        
        $this->artisan('test:email-config')
            ->expectsOutput('Current APP_URL: http://localhost:8000')
            ->expectsOutput('Generated URL for admin orders: http://localhost:8000/admin/orders/1')
            ->assertExitCode(0);
    }

    /**
     * Test command with production environment
     */
    public function test_command_works_in_production_environment(): void
    {
        Config::set('app.env', 'production');
        Config::set('app.url', 'https://hanayashop.com');
        Config::set('mail.mailers.smtp.host', 'smtp.gmail.com');
        
        $this->artisan('test:email-config')
            ->assertExitCode(0);
    }

    /**
     * Test command can be called programmatically
     */
    public function test_command_can_be_called_programmatically(): void
    {
        $result = $this->artisan('test:email-config');
        
        $result->assertSuccessful();
    }

    /**
     * Test command with sendmail driver
     */
    public function test_command_with_sendmail_driver(): void
    {
        Config::set('mail.default', 'sendmail');
        
        $this->artisan('test:email-config')
            ->expectsOutput('- MAIL_MAILER: sendmail')
            ->assertExitCode(0);
    }

    /**
     * Test command with log driver
     */
    public function test_command_with_log_driver(): void
    {
        Config::set('mail.default', 'log');
        
        $this->artisan('test:email-config')
            ->expectsOutput('- MAIL_MAILER: log')
            ->assertExitCode(0);
    }
}
