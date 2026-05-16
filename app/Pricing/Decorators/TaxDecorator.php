<?php

namespace App\Pricing\Decorators;

use App\Pricing\PriceCalculatorInterface;
use App\Pricing\PriceContext;
use App\Pricing\PriceDecorator;

class TaxDecorator extends PriceDecorator
{
    public function __construct(
        PriceCalculatorInterface $next,
        private float $rate,
    ) {
        parent::__construct($next);
    }

    public function calculate(PriceContext $context): PriceContext
    {
        $ctx = $this->next->calculate($context);
        $ctx->tax = round($ctx->subtotal * $this->rate, 2);
        return $ctx;

    }
}
