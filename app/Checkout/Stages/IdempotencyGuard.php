<?php

namespace App\Checkout\Stages;

use App\Checkout\CheckoutContext;
use App\Models\Order;

class IdempotencyGuard
{
    public function handle(CheckoutContext $context, \Closure $next)
    {
        if (! $context->idempotencyKey) {
            return $next($context);
        }

        $existing = Order::where([
            ['idempotency_key', $context->idempotencyKey],
            ['user_id', $context->user->id],
        ])->first();

        if ($existing) {
            $context->order = $existing;

            return $context;
        }

        return $next($context);
    }
}
