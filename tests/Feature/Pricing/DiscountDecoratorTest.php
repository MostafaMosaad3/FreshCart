<?php

namespace Tests\Feature\Pricing;

use App\Models\Coupon;
use App\Pricing\Decorators\DiscountDecorator;
use App\Pricing\Discounts\DiscountStrategyFactory;
use App\Pricing\PriceCalculatorInterface;
use App\Pricing\PriceContext;
use Tests\TestCase;

class DiscountDecoratorTest extends TestCase
{
    private function stubNext(float $subtotal): PriceCalculatorInterface
    {
        return new class($subtotal) implements PriceCalculatorInterface
        {
            public function __construct(private float $subtotal) {}

            public function calculate(PriceContext $context): PriceContext
            {
                $context->subtotal = $this->subtotal;

                return $context;
            }
        };
    }

    private function decorator(float $subtotal): DiscountDecorator
    {
        return new DiscountDecorator(
            $this->stubNext($subtotal),
            new DiscountStrategyFactory(config('discounts.strategies')),
        );
    }

    public function test_no_coupon_means_no_discount(): void
    {
        $context = $this->decorator(1000.0)->calculate(new PriceContext(
            items: collect(), address: null, coupon: null
        ));

        $this->assertSame(0.0, $context->discount);
    }

    public function test_percent_coupon_applies_percentage_of_subtotal(): void
    {
        $coupon = new Coupon(['strategy' => 'percentage', 'config' => ['value' => 10]]);

        $context = $this->decorator(1000.0)->calculate(new PriceContext(
            items: collect(), address: null, coupon: $coupon
        ));

        $this->assertSame(100.0, $context->discount);
        $this->assertSame('10% off', $context->discountDescription);
    }

    public function test_fixed_coupon_applies_fixed_amount(): void
    {
        $coupon = new Coupon(['strategy' => 'fixed_amount', 'config' => ['value' => 50]]);

        $context = $this->decorator(1000.0)->calculate(new PriceContext(
            items: collect(), address: null, coupon: $coupon
        ));

        $this->assertSame(50.0, $context->discount);
    }

    public function test_fixed_coupon_is_capped_at_subtotal(): void
    {
        // Coupon worth more than the cart — discount must not exceed subtotal
        $coupon = new Coupon(['strategy' => 'fixed_amount', 'config' => ['value' => 5000]]);

        $context = $this->decorator(800.0)->calculate(new PriceContext(
            items: collect(), address: null, coupon: $coupon
        ));

        $this->assertSame(800.0, $context->discount);
    }
}
