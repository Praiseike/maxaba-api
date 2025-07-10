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
        ]);

        return $this->respondWithSuccess('Notifications retrieved successfully', $notifications);
    }
}
