<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;

class NotificationsController extends ApiController
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Fetch both read and unread notifications for the authenticated user, sorted by created_at desc
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->get(['id', 'data', 'read_at', 'created_at']);

        // Mark only unread ones as read so they are updated, but keep returning them
        foreach ($notifications as $notification) {
            if (is_null($notification->read_at) || $notification->read_at == false) {
                $notification->markAsRead();
                $notification->read_at = now();
            }
        }

        return $this->respondWithSuccess('Notifications retrieved successfully', $notifications);
    }

    public function getNotificationStats()
    {
        $user = auth()->user();

        $unreadMessagesCount = Conversation::where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('recipient_id', $user->id);
            })
            ->whereHas('messages', function ($query) use ($user) {
                $query->where('user_id', '!=', $user->id)
                      ->whereNull('read_at');
            })->count();

        return $this->respondWithSuccess("Fetched notification stats", [
            'unread_count' => $user->notifications()->whereNull('read_at')->count(),
            'unread_messages_count' => $unreadMessagesCount,
        ]);
    }
}
