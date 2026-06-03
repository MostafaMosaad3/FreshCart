<?php

namespace Tests\Feature\Coupons;

use App\Exceptions\InvalidDiscountConfigException;
use App\Pricing\Discounts\PercentageDiscount;
use App\Pricing\PriceContext;
use Tests\TestCase;

class PercentageDiscountTest extends TestCase
{
    public function test_gives_20_percent_off_a_1000_subtotal(): void
    {
        $strategy = new PercentageDiscount(['value' => 20]);

        $this->assertSame(200.0, $strategy->calculate(new PriceContext(items: collect(), subtotal: 1000)));
    }

    public function test_caps_100_percent_at_the_subtotal(): void
    {
        $strategy = new PercentageDiscount(['value' => 100]);

        $this->assertSame(500.0, $strategy->calculate(new PriceContext(items: collect(), subtotal: 500)));
    }

    public function test_throws_on_negative_percent_config(): void
    {
        $this->expectException(InvalidDiscountConfigException::class);

        (new PercentageDiscount(['value' => -5]))->calculate(new PriceContext(items: collect(), subtotal: 100));
    }

    public function test_returns_zero_when_subtotal_is_zero(): void
    {
        $strategy = new PercentageDiscount(['value' => 20]);

        $this->assertSame(0.0, $strategy->calculate(new PriceContext(items: collect(), subtotal: 0)));
    }

    public function test_describe_reads_the_config_value(): void
    {
        $this->assertSame('20% off', (new PercentageDiscount(['value' => 20]))->describe());
    }
}
