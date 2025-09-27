<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Events\CallInitiated;
use App\Events\CallAccepted;
use App\Events\CallEnded;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class CallController extends Controller
{
    /**
     * Initiate a call
     */
    public function initiateCall(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'call_type' => 'required|in:voice,video'
        ]);

        $caller = Auth::user();
        $receiverId = $request->receiver_id;
        $callType = $request->call_type;

        // Check if receiver exists and get their info
        $receiver = User::find($receiverId);
        if (!$receiver) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Check if receiver is online
        if (!$receiver->isOnline()) {
            return response()->json([
                'success' => false,
                'message' => 'User is currently offline',
                'receiver_status' => 'offline'
            ], 400);
        }

        // Generate unique room name using UUIDs for better security
        $roomName = 'call_' . $caller->uuid . '_' . $receiver->uuid . '_' . time();

        $callData = [
            'call_id' => Str::uuid(),
            'room_name' => $roomName,
            'caller' => [
                'id' => $caller->id,
                'uuid' => $caller->uuid,
                'name' => $caller->name,
                'first_name' => $caller->first_name,
                'last_name' => $caller->last_name,
                'profile_image_url' => $caller->profile_image_url,
                'account_type' => $caller->account_type
            ],
            'receiver' => [
                'id' => $receiver->id,
                'uuid' => $receiver->uuid,
                'name' => $receiver->name,
                'first_name' => $receiver->first_name,
                'last_name' => $receiver->last_name,
                'profile_image_url' => $receiver->profile_image_url
            ],
            'call_type' => $callType,
            'timestamp' => now()->toISOString()
        ];

        // Broadcast call initiation event
        broadcast(new CallInitiated($callData, $receiverId));
        
        return response()->json([
            'success' => true,
            'call_data' => $callData,
            'message' => 'Call initiated successfully'
        ]);
    }

    /**
     * Accept a call
     */
    public function acceptCall(Request $request)
    {
        $request->validate([
            'call_id' => 'required',
            'room_name' => 'required',
            'caller_id' => 'required|exists:users,id'
        ]);

        $receiver = Auth::user();
        $callId = $request->call_id;
        $roomName = $request->room_name;
        $callerId = $request->caller_id;

        $responseData = [
            'call_id' => $callId,
            'room_name' => $roomName,
            'accepted_by' => [
                'id' => $receiver->id,
                'uuid' => $receiver->uuid,
                'name' => $receiver->name,
                'first_name' => $receiver->first_name,
                'last_name' => $receiver->last_name,
                'profile_image_url' => $receiver->profile_image_url
            ],
            'status' => 'accepted',
            'timestamp' => now()->toISOString()
        ];

        // Broadcast call accepted event
        broadcast(new CallAccepted($responseData, $callerId));

        return response()->json([
            'success' => true,
            'message' => 'Call accepted',
            'call_data' => $responseData
        ]);
    }

    /**
     * Reject or end a call
     */
    public function endCall(Request $request)
    {
        $request->validate([
            'call_id' => 'required',
            'other_user_id' => 'required|exists:users,id',
            'reason' => 'required|in:rejected,ended,busy,no_answer'
        ]);

        $user = Auth::user();
        $callId = $request->call_id;
        $otherUserId = $request->other_user_id;
        $reason = $request->reason;

        $endData = [
            'call_id' => $callId,
            'ended_by' => [
                'id' => $user->id,
                'uuid' => $user->uuid,
                'name' => $user->name
            ],
            'reason' => $reason,
            'timestamp' => now()->toISOString()
        ];

        // Broadcast call ended event
        broadcast(new CallEnded($endData, $otherUserId));

        return response()->json([
            'success' => true,
            'message' => 'Call ended',
            'data' => $endData
        ]);
    }

    /**
     * Get user's call contacts (following/followers or agents)
     */
    public function getCallContacts()
    {
        $user = Auth::user();
        
        if ($user->isAgent()) {
            // For agents, get users they can call (recent interactions, etc.)
            $contacts = User::users()
                ->online() // Only show online users for calling
                ->where('id', '!=', $user->id)
                ->select('id', 'uuid', 'first_name', 'last_name', 'profile_image', 'last_seen_at')
                ->limit(20)
                ->get();
        } else {
            // For users, get agents they follow or have interacted with
            $contacts = $user->following()
                ->agents()
                ->online()
                ->select('users.id', 'users.uuid', 'users.first_name', 'users.last_name', 'users.profile_image', 'users.last_seen_at')
                ->get();
                
            // Also include agents from properties they've favorited or viewed
            $agentIds = $user->favourites()
                ->with('user:id,uuid,first_name,last_name,profile_image,last_seen_at')
                ->get()
                ->pluck('user')
                ->filter()
                ->pluck('id')
                ->unique();
                
            $additionalAgents = User::whereIn('id', $agentIds)
                ->online()
                ->get();
                
            $contacts = $contacts->merge($additionalAgents)->unique('id');
        }

        return response()->json([
            'success' => true,
            'contacts' => $contacts->map(function ($contact) {
                return [
                    'id' => $contact->id,
                    'uuid' => $contact->uuid,
                    'name' => $contact->name,
                    'first_name' => $contact->first_name,
                    'last_name' => $contact->last_name,
                    'profile_image_url' => $contact->profile_image_url,
                    'is_online' => $contact->isOnline(),
                    'last_seen' => $contact->last_seen,
                    'account_type' => $contact->account_type
                ];
            })
        ]);
    }

    /**
     * Update user's last seen for call availability
     */
    public function updatePresence()
    {
        $user = Auth::user();
        $user->update(['last_seen_at' => now()]);
        
        return response()->json([
            'success' => true,
            'message' => 'Presence updated'
        ]);
    }

    /**
     * Check if user is available for calls
     */
    public function checkAvailability(User $user)
    {
        return response()->json([
            'available' => $user->isOnline(),
            'last_seen' => $user->last_seen,
            'status' => $user->isOnline() ? 'online' : 'offline'
        ]);
    }

    /**
     * Get Jitsi configuration
     */
    public function getJitsiConfig()
    {
        return response()->json([
            'domain' => config('services.jitsi.domain', 'meet.jit.si'),
            'options' => [
                'width' => '100%',
                'height' => '100%',
                'parentNode' => null, // Will be set by frontend
                'configOverwrite' => [
                    'startWithAudioMuted' => false,
                    'startWithVideoMuted' => false,
                    'enableWelcomePage' => false,
                    'prejoinPageEnabled' => false,
                    'disableInviteFunctions' => true,
                    'toolbarButtons' => [
                        'microphone', 'camera', 'hangup', 'tileview', 'toggle-camera'
                    ],
                ],
                'interfaceConfigOverwrite' => [
                    'TOOLBAR_BUTTONS' => [
                        'microphone', 'camera', 'hangup', 'tileview'
                    ],
                    'SHOW_JITSI_WATERMARK' => false,
                    'SHOW_WATERMARK_FOR_GUESTS' => false,
                    'SHOW_BRAND_WATERMARK' => false,
                ]
            ]
        ]);
    }
}