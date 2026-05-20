<?php

namespace App\Pricing\Decorators;

use App\Pricing\PriceContext;
use App\Pricing\PriceDecorator;

class DiscountDecorator extends PriceDecorator
{
    public function calculate(PriceContext $context): PriceContext
    {
        $context = $this->next->calculate($context);

        if (! $context->coupon) {
            return $context;
        }

        $context->discount = match ($context->coupon->type) {
            'percent' => round($context->subtotal * ($context->coupon->value / 100), 2),
            'fixed' => min((float) $context->coupon->value, $context->subtotal),
            default => 0.0,
        };

        return $context;
    }
}
