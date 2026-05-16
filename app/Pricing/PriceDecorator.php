<?php

namespace App\Pricing;

abstract class PriceDecorator  implements PriceCalculatorInterface
{
    public function __construct(PriceCalculatorInterface $next){}
}
