<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallAccepted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $responseData;
    public $callerId;

    public function __construct(array $responseData, int $callerId)
    {
        $this->responseData = $responseData;
        $this->callerId = $callerId;
    }

    public function broadcastOn()
    {
        return new Channel('user.' . $this->callerId);
    }

    public function broadcastAs()
    {
        return 'call-accepted';
    }

    public function broadcastWith()
    {
        return $this->responseData;
    }
}
