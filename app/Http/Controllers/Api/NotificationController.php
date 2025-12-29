<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // List notifications for authenticated user
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = (int) $request->get('per_page', 20);
        $perPage = max(1, min(200, $perPage));

        $notifications = $user->notifications()->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => NotificationResource::collection($notifications)->response()->getData(true),
        ]);
    }

    // Show single notification
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->first();

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        return response()->json(['success' => true, 'data' => new NotificationResource($notification)]);
    }

    // Mark one notification as read
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->first();
        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        $notification->markAsRead();

        return response()->json(['success' => true, 'message' => 'Marked as read', 'data' => new NotificationResource($notification)]);
    }

    // Mark all notifications as read
    public function markAllRead(Request $request)
    {
        $user = $request->user();
        foreach ($user->unreadNotifications as $n) {
            $n->markAsRead();
        }

        return response()->json(['success' => true, 'message' => 'All notifications marked as read']);
    }

    // Get unread count
    public function unreadCount(Request $request)
    {
        $user = $request->user();
        $count = $user->unreadNotifications()->count();

        return response()->json(['success' => true, 'data' => ['unread' => $count]]);
    }
}
