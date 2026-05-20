<?php

namespace App\Pricing;

use App\Models\Address;
use App\Models\Coupon;
use Illuminate\Support\Collection;

final class PriceContext
{
    public function __construct(
        public readonly Collection $items,
        public readonly ?Address $address = null,
        public readonly ?Coupon $coupon = null,
        public float $subtotal = 0.0,
        public float $tax = 0.0,
        public float $shipping = 0.0,
        public float $discount = 0.0,
    ) {}

    public function total(): float
    {
        return round($this->subtotal + $this->tax + $this->shipping - $this->discount, 2);
    }
}
