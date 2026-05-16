<?php

namespace App\Pricing;

interface PriceCalculatorInterface
{
    public function calculate(PriceContext $context) :PriceContext;
}
