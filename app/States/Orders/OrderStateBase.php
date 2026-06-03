<?php

namespace App\States\Orders;

use App\Exceptions\IllegalTransitionException;
use App\Models\Order;

abstract class OrderStateBase implements OrderState
{
    public function markAsPaid(Order $order): void
    {
        $this->illegal('markAsPaid');
    }

    public function markAsShipped(Order $order, ?string $trackingNumber = null): void
    {
        $this->illegal('markAsShipped');
    }

    public function markAsDelivered(Order $order): void
    {
        $this->illegal('markAsDelivered');
    }

    public function cancel(Order $order, ?string $reason = null): void
    {
        $this->illegal('cancel');
    }

    protected function illegal(string $action): void
    {
        throw new IllegalTransitionException(
            sprintf('Cannot %s from %s', $action, static::class)
        );
    }
}
