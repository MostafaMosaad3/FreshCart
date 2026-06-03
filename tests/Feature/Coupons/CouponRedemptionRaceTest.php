<?php

namespace Tests\Feature\Coupons;

use App\Checkout\CheckoutContext;
use App\Checkout\Stages\RedeemCoupon;
use App\Exceptions\CouponMaxedOutException;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Order;
use App\Models\User;
use App\Pricing\PriceContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponRedemptionRaceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build a CheckoutContext carrying a created order plus a PriceContext
     * with the given coupon attached.
     */
    private function makeCheckoutContextWithCoupon(Coupon $coupon): CheckoutContext
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $context = new CheckoutContext(user: $user);
        $context->order = $order;
        $context->priceContext = new PriceContext(
            items: collect(),
            coupon: $coupon,
            discount: 10,
        );

        return $context;
    }

    public function test_guards_against_coupon_over_redemption_under_concurrent_checkout(): void
    {
        $coupon = Coupon::factory()->create([
            'max_uses' => 1,
            'used_count' => 0,
            'strategy' => 'percentage',
            'config' => ['value' => 10],
        ]);

        $stage = new RedeemCoupon;
        $ctxA = $this->makeCheckoutContextWithCoupon($coupon);
        $ctxB = $this->makeCheckoutContextWithCoupon($coupon);

        $stage->handle($ctxA, fn ($c) => $c);   // first: ok

        $this->expectException(CouponMaxedOutException::class);

        try {
            $stage->handle($ctxB, fn ($c) => $c);   // second: guard fires
        } finally {
            $this->assertSame(1, CouponRedemption::count());
            $this->assertSame(1, $coupon->fresh()->used_count);
        }
    }
}
