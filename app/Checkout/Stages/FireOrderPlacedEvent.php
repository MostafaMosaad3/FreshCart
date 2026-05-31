<?php

namespace App\Checkout\Stages;

use App\Checkout\CheckoutContext;
use App\Events\Orders\OrderPlaced;

class FireOrderPlacedEvent
{
    public function handle(CheckoutContext $context, \Closure $next)
    {
        event(new OrderPlaced($context->order, $next));

        return $next($context);
    }
}
