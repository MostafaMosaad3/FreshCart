<?php

namespace App\Pricing\Discounts;

abstract class AbstractDiscount implements DiscountStrategyInterface
{
    public function __construct(protected array $config) {}

    protected function cap(float $discount, float $subtotal): float
    {
        return min(max($discount, 0), $subtotal);
    }
}
