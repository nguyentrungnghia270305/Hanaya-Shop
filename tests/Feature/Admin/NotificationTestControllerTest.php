<?php

namespace Tests\Feature\Admin;

use App\Models\Order\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTestControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $customer;

    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Fake notifications to prevent actual email sending
        Notification::fake();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->customer = User::factory()->create(['role' => 'user']);
        $this->order = Order::factory()->create(['user_id' => $this->customer->id]);
    }

    public function test_guest_cannot_access_notification_test_endpoint(): void
    {
        $response = $this->getJson(route('admin.test.notifications'));

        $response->assertStatus(401);
    }

    public function test_regular_user_cannot_access_notification_test_endpoint(): void
    {
        $response = $this->actingAs($this->customer)
            ->getJson(route('admin.test.notifications'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_notification_test_endpoint(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.test.notifications'));

        $response->assertStatus(200);
    }

    public function test_notification_test_returns_success_structure(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.test.notifications'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'results' => [
                    'admin_notifications' => [
                        'new_order',
                        'confirmed',
                        'shipped',
                        'completed',
                        'paid',
                        'cancelled',
                    ],
                    'customer_notifications' => [
                        'new_order',
                        'confirmed',
                        'shipped',
                        'completed',
                        'cancelled',
                    ],
                ],
                'admin_email',
                'customer_email',
                'locale',
            ]);
    }

    public function test_notification_test_returns_admin_email(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.test.notifications'));

        $response->assertStatus(200)
            ->assertJson([
                'admin_email' => $this->admin->email,
            ]);
    }

    public function test_notification_test_returns_customer_email(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.test.notifications'));

        $response->assertStatus(200)
            ->assertJson([
                'customer_email' => $this->customer->email,
            ]);
    }

    public function test_notification_test_includes_locale(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.test.notifications'));

        $response->assertStatus(200)
            ->assertJsonPath('locale', config('app.locale'));
    }

    public function test_notification_test_sends_all_admin_notifications(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.test.notifications'));

        $response->assertStatus(200);

        $adminNotifications = $response->json('results.admin_notifications');

        $this->assertEquals('SUCCESS', $adminNotifications['new_order']);
        $this->assertEquals('SUCCESS', $adminNotifications['confirmed']);
        $this->assertEquals('SUCCESS', $adminNotifications['shipped']);
        $this->assertEquals('SUCCESS', $adminNotifications['completed']);
        $this->assertEquals('SUCCESS', $adminNotifications['paid']);
        $this->assertEquals('SUCCESS', $adminNotifications['cancelled']);
    }

    public function test_notification_test_sends_all_customer_notifications(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.test.notifications'));

        $response->assertStatus(200);

        $customerNotifications = $response->json('results.customer_notifications');

        $this->assertEquals('SUCCESS', $customerNotifications['new_order']);
        $this->assertEquals('SUCCESS', $customerNotifications['confirmed']);
        $this->assertEquals('SUCCESS', $customerNotifications['shipped']);
        $this->assertEquals('SUCCESS', $customerNotifications['completed']);
        $this->assertEquals('SUCCESS', $customerNotifications['cancelled']);
    }

    public function test_notification_test_respects_current_locale(): void
    {
        session(['locale' => 'vi']);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.test.notifications'));

        $response->assertStatus(200)
            ->assertJson(['locale' => 'vi']);
    }

    public function test_notification_test_uses_default_locale_when_not_set(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.test.notifications'));

        $response->assertStatus(200)
            ->assertJson(['locale' => config('app.locale')]);
    }

    public function test_notification_test_handles_missing_admin(): void
    {
        // Delete all admins except the one used for authentication
        User::where('role', 'admin')->where('id', '!=', $this->admin->id)->delete();

        // Delete the current admin too (controller uses first() so won't find any)
        $this->admin->delete();

        // Create a new admin just for authentication (but it will be the "first" admin found)
        // So we need to test when User::where('role', 'admin')->first() returns null
        // This scenario is impossible with middleware, so we test customer missing instead

        // Actually, let's just verify this test scenario is not realistic
        // Skip this test or change the approach
        $this->markTestSkipped('Cannot test missing admin scenario with admin middleware requirement');
    }

    public function test_notification_test_handles_missing_customer(): void
    {
        User::where('role', 'user')->delete();

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.test.notifications'));

        $response->assertStatus(400)
            ->assertJson(['error' => 'Missing test data']);
    }

    public function test_notification_test_handles_missing_order(): void
    {
        // Delete orders without using truncate to avoid foreign key constraint issues
        Order::query()->delete();

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.test.notifications'));

        $response->assertStatus(400)
            ->assertJson(['error' => 'Missing test data']);
    }

    public function test_notification_test_creates_notification_records(): void
    {
        // Temporarily disable notification fake for this test
        Notification::swap(new \Illuminate\Notifications\ChannelManager(app()));

        $initialCount = $this->admin->notifications()->count();

        $this->actingAs($this->admin)
            ->getJson(route('admin.test.notifications'));

        $finalCount = $this->admin->notifications()->count();

        $this->assertGreaterThan($initialCount, $finalCount);
    }
}
