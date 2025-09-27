<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $endData;
    public $otherUserId;

    public function __construct(array $endData, int $otherUserId)
    {
        $this->endData = $endData;
        $this->otherUserId = $otherUserId;
    }

    public function broadcastOn()
    {
        return new Channel('user.' . $this->otherUserId);
    }

    public function broadcastAs()
    {
        return 'call-ended';
    }

    public function broadcastWith()
    {
        return $this->endData;
    }
}