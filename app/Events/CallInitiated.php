<?php
namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallInitiated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $callData;
    public $receiverId;

    public function __construct(array $callData, int $receiverId)
    {
        \Log::info('CallInitiated event constructed with receiverId: ' . $receiverId);
        $this->callData = $callData;
        $this->receiverId = $receiverId;
    }

    public function broadcastOn()
    {
        return new Channel('user.' . $this->receiverId);
    }

    public function broadcastAs()
    {
        return 'incoming-call';
    }

    public function broadcastWith()
    {
        return $this->callData;
    }
}
