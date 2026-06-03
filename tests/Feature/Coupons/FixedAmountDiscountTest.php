<?php

namespace Tests\Feature\Coupons;

use App\Exceptions\InvalidDiscountConfigException;
use App\Pricing\Discounts\FixedAmountDiscount;
use App\Pricing\PriceContext;
use Tests\TestCase;

class FixedAmountDiscountTest extends TestCase
{
    public function test_subtracts_a_fixed_amount(): void
    {
        $strategy = new FixedAmountDiscount(['value' => 50]);

        $this->assertSame(50.0, $strategy->calculate(new PriceContext(items: collect(), subtotal: 200)));
    }

    public function test_caps_fixed_amount_at_the_subtotal(): void
    {
        $strategy = new FixedAmountDiscount(['value' => 500]);

        $this->assertSame(200.0, $strategy->calculate(new PriceContext(items: collect(), subtotal: 200)));
    }

    public function test_throws_on_non_positive_value(): void
    {
        $this->expectException(InvalidDiscountConfigException::class);

        (new FixedAmountDiscount(['value' => 0]))->calculate(new PriceContext(items: collect(), subtotal: 100));
    }

    public function test_describe_formats_the_amount(): void
    {
        $this->assertSame('50.00 EGP off', (new FixedAmountDiscount(['value' => 50]))->describe());
    }
}
