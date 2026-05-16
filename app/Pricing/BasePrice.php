<?php

namespace App\Pricing;

class BasePrice implements PriceCalculatorInterface
{

    public function calculate(PriceContext $context): PriceContext
    {
        $context->subtotal = $context->items->sum(fn($item) => $item->unit_price * $item->quantity);
        return $context;
    }
}
