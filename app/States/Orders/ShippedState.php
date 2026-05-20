<?php

namespace App\States\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;

class ShippedState extends OrderStateBase
{
    public function markAsDelivered(Order $order): void
    {
        $order->status = OrderStatus::Delivered;
        $order->delivered_at = now();
        $order->save();
    }
}
