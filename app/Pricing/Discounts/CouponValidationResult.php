<?php

namespace App\Pricing\Discounts;

class CouponValidationResult
{
    public function __construct(
        public readonly bool $ok,
        public readonly ?string $reason,
    ) {}

}
