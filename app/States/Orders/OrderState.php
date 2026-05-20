<?php

namespace App\States\Orders;

use App\Models\Order;

interface OrderState
{
    public function markAsPaid(Order $order): void;

    public function markAsShipped(Order $order, ?string $trackingNumber = null): void;

    public function markAsDelivered(Order $order): void;

    public function cancel(Order $order, ?string $reason = null): void;
}
