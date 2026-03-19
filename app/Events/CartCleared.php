<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CartCleared implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('user.' . $this->userId);
    }

    public function broadcastAs(): string
    {
        return 'cart.cleared';
    }

    public function broadcastWith(): array
    {
        return ['user_id' => $this->userId];
    }
}
