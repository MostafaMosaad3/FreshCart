<?php

namespace App\Checkout\Stages;

use App\Checkout\CheckoutContext;
use App\Exceptions\CouponMaxedOutException;
use App\Models\Coupon;
use App\Models\CouponRedemption;

class RedeemCoupon
{
    public function handle(CheckoutContext $context, \Closure $next)
    {
        $coupon = $context->priceContext->coupon;

        if (! $coupon) {
            return $next($context);
        }

        // Atomic increment guarded by the max-uses ceiling. The DB serializes
        // concurrent updates, so two simultaneous checkouts cannot both pass.
        $rows = Coupon::where('id', $coupon->id)
            ->when($coupon->max_uses !== null, fn ($q) => $q->whereRaw('used_count < max_uses'))
            ->increment('used_count');

        if ($rows === 0) {
            throw new CouponMaxedOutException;   // caught -> 422 + transaction rollback
        }

        CouponRedemption::create([
            'coupon_id' => $coupon->id,
            'user_id' => $context->order->user_id,
            'order_id' => $context->order->id,
            'discount_amount' => $context->priceContext->discount,
            'redeemed_at' => now(),
        ]);

        return $next($context);
    }
}
