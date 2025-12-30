<?php

namespace Tests\Unit\App\Controllers\Common;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'admin']);
    }

    /**
     * Test markAsRead marks notification as read successfully
     */
    public function test_mark_as_read_marks_notification_successfully(): void
    {
        $this->actingAs($this->user);

        // Create a notification
        $notification = $this->user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['message' => 'Test notification'],
            'read_at' => null,
        ]);

        $response = $this->postJson(route('admin.notifications.markRead'), [
            'id' => $notification->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    /**
     * Test markAsRead returns 401 when not authenticated
     */
    public function test_mark_as_read_returns_401_when_not_authenticated(): void
    {
        $response = $this->postJson(route('admin.notifications.markRead'), [
            'id' => 'some-uuid',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    /**
     * Test markAsRead returns 404 for non-existent notification
     */
    public function test_mark_as_read_returns_404_for_non_existent_notification(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('admin.notifications.markRead'), [
            'id' => 'non-existent-uuid',
        ]);

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Notification not found']);
    }

    /**
     * Test markAsRead returns 404 for notification belonging to another user
     */
    public function test_mark_as_read_returns_404_for_other_users_notification(): void
    {
        $this->actingAs($this->user);

        $otherUser = User::factory()->create();
        $notification = $otherUser->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['message' => 'Test notification'],
            'read_at' => null,
        ]);

        $response = $this->postJson(route('admin.notifications.markRead'), [
            'id' => $notification->id,
        ]);

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Notification not found']);
    }

    /**
     * Test markAsRead returns 404 for already read notification
     */
    public function test_mark_as_read_returns_404_for_already_read_notification(): void
    {
        $this->actingAs($this->user);

        $notification = $this->user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['message' => 'Test notification'],
            'read_at' => now(),
        ]);

        $response = $this->postJson(route('admin.notifications.markRead'), [
            'id' => $notification->id,
        ]);

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Notification not found']);
    }

    /**
     * Test markAsRead updates read_at timestamp
     */
    public function test_mark_as_read_updates_read_at_timestamp(): void
    {
        $this->actingAs($this->user);

        $notification = $this->user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['message' => 'Test notification'],
            'read_at' => null,
        ]);

        $beforeTime = now();
        $this->postJson(route('admin.notifications.markRead'), ['id' => $notification->id]);
        $afterTime = now();

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
        $this->assertTrue($notification->read_at->between($beforeTime->subSecond(), $afterTime->addSecond()));
    }

    /**
     * Test markAsRead with invalid UUID format
     */
    public function test_mark_as_read_with_invalid_uuid_format(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('admin.notifications.markRead'), [
            'id' => 'invalid-uuid-format',
        ]);

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Notification not found']);
    }

    /**
     * Test markAsRead only affects specified notification
     */
    public function test_mark_as_read_only_affects_specified_notification(): void
    {
        $this->actingAs($this->user);

        $notification1 = $this->user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['message' => 'Test notification 1'],
            'read_at' => null,
        ]);

        $notification2 = $this->user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['message' => 'Test notification 2'],
            'read_at' => null,
        ]);

        $this->postJson(route('admin.notifications.markRead'), ['id' => $notification1->id]);

        $notification1->refresh();
        $notification2->refresh();

        $this->assertNotNull($notification1->read_at);
        $this->assertNull($notification2->read_at);
    }

    /**
     * Test markAsRead returns JSON response
     */
    public function test_mark_as_read_returns_json_response(): void
    {
        $this->actingAs($this->user);

        $notification = $this->user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['message' => 'Test notification'],
            'read_at' => null,
        ]);

        $response = $this->postJson(route('admin.notifications.markRead'), [
            'id' => $notification->id,
        ]);

        $response->assertHeader('Content-Type', 'application/json');
    }

    /**
     * Test markAsRead with multiple unread notifications
     */
    public function test_mark_as_read_with_multiple_unread_notifications(): void
    {
        $this->actingAs($this->user);

        $notifications = collect();
        for ($i = 0; $i < 5; $i++) {
            $notifications->push($this->user->notifications()->create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'App\Notifications\TestNotification',
                'data' => ['message' => "Test notification $i"],
                'read_at' => null,
            ]));
        }

        $targetNotification = $notifications->get(2);

        $response = $this->postJson(route('admin.notifications.markRead'), [
            'id' => $targetNotification->id,
        ]);

        $response->assertStatus(200);

        $targetNotification->refresh();
        $this->assertNotNull($targetNotification->read_at);

        // Other notifications should remain unread
        $this->assertEquals(4, $this->user->unreadNotifications->count());
    }

    /**
     * Test markAsRead with empty notification ID
     */
    public function test_mark_as_read_with_empty_id(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('admin.notifications.markRead'), [
            'id' => '',
        ]);

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Notification not found']);
    }

    /**
     * Test markAsRead with null notification ID
     */
    public function test_mark_as_read_with_null_id(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson(route('admin.notifications.markRead'), [
            'id' => null,
        ]);

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Notification not found']);
    }

    /**
     * Test markAsRead requires authentication middleware
     */
    public function test_mark_as_read_requires_authentication(): void
    {
        $notification = $this->user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['message' => 'Test notification'],
            'read_at' => null,
        ]);

        // Not authenticated
        $response = $this->postJson(route('admin.notifications.markRead'), [
            'id' => $notification->id,
        ]);

        $response->assertStatus(401);

        $notification->refresh();
        $this->assertNull($notification->read_at);
    }

    /**
     * Test markAsRead can be called via AJAX
     */
    public function test_mark_as_read_works_with_ajax_request(): void
    {
        $this->actingAs($this->user);

        $notification = $this->user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['message' => 'Test notification'],
            'read_at' => null,
        ]);

        $response = $this->postJson(route('admin.notifications.markRead'), [
            'id' => $notification->id,
        ], ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);
    }

    /**
     * Test markAsRead idempotency - calling twice doesn't cause errors
     */
    public function test_mark_as_read_idempotency(): void
    {
        $this->actingAs($this->user);

        $notification = $this->user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\TestNotification',
            'data' => ['message' => 'Test notification'],
            'read_at' => null,
        ]);

        // First call - marks as read
        $response1 = $this->postJson(route('admin.notifications.markRead'), [
            'id' => $notification->id,
        ]);
        $response1->assertStatus(200);

        // Second call - notification already read, returns 404
        $response2 = $this->postJson(route('admin.notifications.markRead'), [
            'id' => $notification->id,
        ]);
        $response2->assertStatus(404);
    }
}
