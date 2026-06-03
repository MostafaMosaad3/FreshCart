<?php

namespace App\Pricing\Discounts;

use App\Exceptions\UnknownDiscountStrategyException;
use App\Models\Coupon;

class DiscountStrategyFactory
{
    public function __construct(private array $strategies) {}

    public function make(Coupon $coupon): DiscountStrategyInterface
    {
        $class = $this->strategies[$coupon->strategy]
            ?? throw new UnknownDiscountStrategyException("Unknown strategy: {$coupon->strategy}");

        return new $class($coupon->config);
    }
}
