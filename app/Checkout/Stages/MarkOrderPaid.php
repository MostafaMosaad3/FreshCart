<?php

namespace App\Checkout\Stages;

use App\Checkout\CheckoutContext;

class MarkOrderPaid
{
    public function handle(CheckoutContext $context, \Closure $next)
    {
        $context->order->markAsPaid();

        return $next($context);
    }
}
