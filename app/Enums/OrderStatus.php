<?php

namespace App\Enums;

use App\States\Orders\CancelledState;
use App\States\Orders\DeliveredState;
use App\States\Orders\OrderState;
use App\States\Orders\PaidState;
use App\States\Orders\PendingState;
use App\States\Orders\ShippedState;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function currentState(): OrderState
    {
        return match ($this) {
            self::Pending => new PendingState,
            self::Paid => new PaidState,
            self::Shipped => new ShippedState,
            self::Delivered => new DeliveredState,
            self::Cancelled => new CancelledState,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending Payment',
            self::Paid => 'Paid',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }
}
