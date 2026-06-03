<?php

namespace App\Pricing\Discounts;

use App\Exceptions\InvalidDiscountConfigException;
use App\Pricing\PriceContext;

class FixedAmountDiscount extends AbstractDiscount
{
    public function calculate(PriceContext $context): float
    {
        $value = (float) ($this->config['value'] ?? 0);
        if ($value <= 0) {
            throw new InvalidDiscountConfigException("Fixed amount must be > 0, got {$value}");
        }

        return $this->cap($value, $context->subtotal);
    }

    public function describe(): string
    {
        return sprintf('%.2f EGP off', (float) ($this->config['value'] ?? 0));
    }
}
