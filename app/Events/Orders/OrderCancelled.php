<?php

namespace App\Events\Orders;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Queue\SerializesModels;

class OrderCancelled implements ShouldDispatchAfterCommit
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Order $order, public ?string $reason = null)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
