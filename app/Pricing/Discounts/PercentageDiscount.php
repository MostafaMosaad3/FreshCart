<?php

namespace App\Pricing\Discounts;

use App\Exceptions\InvalidDiscountConfigException;
use App\Pricing\PriceContext;

class PercentageDiscount extends AbstractDiscount
{
    public function calculate(PriceContext $context): float
    {
        $value = (float) ($this->config['value'] ?? 0);
        if ($value <= 0 || $value > 100) {
            throw new InvalidDiscountConfigException("Percentage must be 0 < value <= 100, got {$value}");
        }

        return $this->cap($context->subtotal * ($value / 100), $context->subtotal);
    }

    public function describe(): string
    {
        return ($this->config['value'] ?? 0).'% off';
    }
}
