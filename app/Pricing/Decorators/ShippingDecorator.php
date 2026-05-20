<?php

namespace App\Pricing\Decorators;

use App\Pricing\PriceContext;
use App\Pricing\PriceDecorator;

class ShippingDecorator extends PriceDecorator
{
    public function calculate(PriceContext $context): PriceContext
    {
        $context = $this->next->calculate($context);
        $threshold = (float) config('commerce.shipping.free_threshold', 2000);
        $flat = (float) config('commerce.shipping.flat_rate', 50);

        $context->shipping = $context->subtotal >= $threshold ? 0.0 : $flat;

        return $context;
    }
}
