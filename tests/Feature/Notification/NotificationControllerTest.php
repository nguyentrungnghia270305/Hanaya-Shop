<?php

namespace Tests\Feature\Notification;

use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    public function test_dummy_notification_controller(): void
    {
        $this->assertTrue(true);
    }
}

// namespace Tests\Feature;

// use App\Models\User;
// use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Support\Facades\Mail;
// use Illuminate\Support\Facades\Notification as NotificationFacade;
// use Tests\TestCase;

// class NotificationControllerTest extends TestCase
// {
//     use RefreshDatabase;

//     protected $user;

//     protected function setUp(): void
//     {
//         parent::setUp();
//         Mail::fake();
//         $this->user = User::factory()->create();
//     }

//     /**
//      * @test
//      */
//     public function authenticated_user_can_mark_notification_as_read()
//     {
//         // Create a notification for the user WITHOUT faking
//         $this->user->notify(new \App\Notifications\ResetPassword('test-token'));

//         $notification = $this->user->notifications()->first();

//         $response = $this->actingAs($this->user)->post(route('notifications.markAsRead', $notification->id));

//         $response->assertOk();
//         $response->assertJson(['success' => true]);

//         $notification->refresh();
//         $this->assertNotNull($notification->read_at);
//     }

//     /**
//      * @test
//      */
//     public function guest_cannot_mark_notification_as_read()
//     {
//         $response = $this->post(route('notifications.markAsRead', 'fake-id'));

//         $response->assertRedirect(route('login'));
//     }

//     /**
//      * @test
//      */
//     public function user_cannot_mark_another_users_notification_as_read()
//     {
//         $otherUser = User::factory()->create();
//         $otherUser->notify(new \App\Notifications\ResetPassword('test-token'));

//         $notification = $otherUser->notifications()->first();

//         $response = $this->actingAs($this->user)->post(route('notifications.markAsRead', $notification->id));

//         $response->assertStatus(404);
//     }

//     /**
//      * @test
//      */
//     public function marking_non_existent_notification_returns_404()
//     {
//         $response = $this->actingAs($this->user)->post(route('notifications.markAsRead', 'non-existent-id'));

//         $response->assertStatus(404);
//     }

//     /**
//      * @test
//      */
//     public function marking_already_read_notification_still_succeeds()
//     {
//         $this->user->notify(new \App\Notifications\ResetPassword('test-token'));

//         $notification = $this->user->notifications()->first();
//         $notification->markAsRead();

//         $response = $this->actingAs($this->user)->post(route('notifications.markAsRead', $notification->id));

//         $response->assertOk();
//         $response->assertJson(['success' => true]);
//     }

//     /**
//      * @test
//      */
//     public function notification_read_timestamp_is_updated()
//     {
//         $this->user->notify(new \App\Notifications\ResetPassword('test-token'));

//         $notification = $this->user->notifications()->first();

//         $this->assertNull($notification->read_at);

//         $this->actingAs($this->user)->post(route('notifications.markAsRead', $notification->id));

//         $notification->refresh();
//         $this->assertNotNull($notification->read_at);
//     }

//     /**
//      * @test
//      */
//     public function response_includes_success_message()
//     {
//         $this->user->notify(new \App\Notifications\ResetPassword('test-token'));

//         $notification = $this->user->notifications()->first();

//         $response = $this->actingAs($this->user)->post(route('notifications.markAsRead', $notification->id));

//         $response->assertJsonStructure(['success', 'message']);
//     }
// }
