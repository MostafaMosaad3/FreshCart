<?php

namespace Tests\Feature\Coupons;

use App\Pricing\Discounts\BuyXGetYDiscount;
use App\Pricing\PriceContext;
use App\Pricing\Support\FakeCart;
use Tests\TestCase;

class BuyXGetYDiscountTest extends TestCase
{
    /**
     * Build a PriceContext whose cart holds the given [product_id, qty, unit_price] rows.
     */
    private function ctxWithItems(array $rows, float $subtotal): PriceContext
    {
        $cart = new FakeCart(array_map(
            fn ($r) => (object) ['product_id' => $r[0], 'quantity' => $r[1], 'unit_price' => $r[2]],
            $rows,
        ));

        return new PriceContext(items: collect(), cart: $cart, subtotal: $subtotal);
    }

    public function test_buy_two_get_one_on_three_identical_items(): void
    {
        $strategy = new BuyXGetYDiscount(['product_ids' => [1], 'buy' => 2, 'get' => 1]);
        $ctx = $this->ctxWithItems([[1, 3, 100.0]], subtotal: 300);

        $this->assertSame(100.0, $strategy->calculate($ctx));
    }

    public function test_zero_discount_when_threshold_not_met(): void
    {
        $strategy = new BuyXGetYDiscount(['product_ids' => [1], 'buy' => 2, 'get' => 1]);
        $ctx = $this->ctxWithItems([[1, 2, 100.0]], subtotal: 200);

        $this->assertSame(0.0, $strategy->calculate($ctx));
    }

    public function test_only_eligible_products_contribute(): void
    {
        // Only product 1 is eligible; the 3 units of product 9 are ignored.
        $strategy = new BuyXGetYDiscount(['product_ids' => [1], 'buy' => 2, 'get' => 1]);
        $ctx = $this->ctxWithItems([[1, 3, 100.0], [9, 3, 100.0]], subtotal: 600);

        $this->assertSame(100.0, $strategy->calculate($ctx));
    }

    public function test_cheapest_unit_is_the_free_one(): void
    {
        $strategy = new BuyXGetYDiscount(['product_ids' => [1, 2, 3], 'buy' => 2, 'get' => 1]);
        $ctx = $this->ctxWithItems([[1, 1, 100.0], [2, 1, 80.0], [3, 1, 60.0]], subtotal: 240);

        $this->assertSame(60.0, $strategy->calculate($ctx));
    }
}
