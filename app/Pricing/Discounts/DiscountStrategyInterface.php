<?php

namespace App\Pricing\Discounts;

use App\Pricing\PriceContext;

interface DiscountStrategyInterface
{
    public function calculate(PriceContext $context): float;

    public function describe(): string;
}
