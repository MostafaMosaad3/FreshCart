<?php

namespace App\Checkout;

use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use App\Pricing\PriceContext;
use Illuminate\Support\Collection;

class CheckoutContext
{
    public function __construct(
        public readonly User $user,
        public readonly ?string $idempotencyKey = null,
    ) {}

    public Cart $cart;
    public Collection $lockedVariants;
    public PriceContext $priceContext;
    public ?array $paymentResult = null;
    public Order $order;
}
