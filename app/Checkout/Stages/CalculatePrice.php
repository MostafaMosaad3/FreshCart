<?php

namespace App\Checkout\Stages;

use App\Checkout\CheckoutContext;
use App\Pricing\PriceCalculatorInterface;
use App\Pricing\PriceContext;

class CalculatePrice
{
    public function __construct(protected PriceCalculatorInterface $calculator) {}

    public function handle(CheckoutContext $context, \Closure $next)
    {
        $context->priceContext = $this->calculator->calculate(
            new PriceContext(
                items: $context->cart->items,
                address: $context->user->defaultAddress,
                coupon: null,
            )
        );

        return $next($context);
    }
}
