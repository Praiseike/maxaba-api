<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationsController extends ApiController
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Fetch notifications for the authenticated user
        $notifications = $user->notifications()->get([
            'id',
            'data',
            'read_at',
            'created_at'
        ])->whereNull('read_at')->sortByDesc('created_at');

        $notifications->map(callback: function ($notification) {
            $notification->markAsRead();
        });

        return $this->respondWithSuccess('Notifications retrieved successfully', $notifications);
    }

    public function getNotificationStats(){
        $user = auth()->user();

        return $this->respondWithSuccess("Fetched notification stats", [
            'unread_count' => $user->notifications()->whereNull('read_at')->count(),
        ]);
    }
}
