<?php

namespace App\Pricing\Decorators;

use App\Pricing\Discounts\DiscountStrategyFactory;
use App\Pricing\PriceCalculatorInterface;
use App\Pricing\PriceContext;
use App\Pricing\PriceDecorator;

class DiscountDecorator extends PriceDecorator
{
    public function __construct(
        PriceCalculatorInterface $next,
        private DiscountStrategyFactory $factory
    ) {
        parent::__construct($next);
    }

    public function calculate(PriceContext $context): PriceContext
    {
        $context = $this->next->calculate($context);

        if (! $context->coupon) {
            return $context;
        }

        $strategy = $this->factory->make($context->coupon);
        $context->discount = $strategy->calculate($context);
        $context->discountDescription = $strategy->describe();

        return $context;
    }
}
