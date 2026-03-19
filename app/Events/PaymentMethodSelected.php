<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class PaymentMethodSelected implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(
        public int    $orderId,
        public string $method,
        public float  $amount,
        public int    $branchId
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('payment.' . $this->branchId);
    }

    public function broadcastAs(): string
    {
        return 'method.selected';
    }
}
