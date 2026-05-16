<?php

namespace Tests\Feature\Pricing;

use App\Models\Coupon;
use App\Pricing\Decorators\DiscountDecorator;
use App\Pricing\PriceCalculatorInterface;
use App\Pricing\PriceContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DiscountDecoratorTest extends TestCase
{
    private function stubNext(float $subtotal): PriceCalculatorInterface
    {
        return new class($subtotal) implements PriceCalculatorInterface {
            public function __construct(private float $subtotal) {}
            public function calculate(PriceContext $context): PriceContext
            {
                $context->subtotal = $this->subtotal;
                return $context;
            }
        };
    }

    public function test_no_coupon_means_no_discount(): void
    {
        $discount = new DiscountDecorator($this->stubNext(1000.0));
        $context = $discount->calculate(new PriceContext(
            items: collect(), address: null, coupon: null
        ));

        $this->assertSame(0.0, $context->discount);
    }

    public function test_percent_coupon_applies_percentage_of_subtotal(): void
    {
        $coupon = new Coupon(['type' => 'percent', 'value' => 10]);

        $discount = new DiscountDecorator($this->stubNext(1000.0));
        $context = $discount->calculate(new PriceContext(
            items: collect(), address: null, coupon: $coupon
        ));

        $this->assertSame(100.0, $context->discount);
    }

    public function test_fixed_coupon_applies_fixed_amount(): void
    {
        $coupon = new Coupon(['type' => 'fixed', 'value' => 50]);

        $discount = new DiscountDecorator($this->stubNext(1000.0));
        $context = $discount->calculate(new PriceContext(
            items: collect(), address: null, coupon: $coupon
        ));

        $this->assertSame(50.0, $context->discount);
    }

    public function test_fixed_coupon_is_capped_at_subtotal(): void
    {
        // Coupon worth more than the cart — discount must not exceed subtotal
        $coupon = new Coupon(['type' => 'fixed', 'value' => 5000]);

        $discount = new DiscountDecorator($this->stubNext(800.0));
        $context = $discount->calculate(new PriceContext(
            items: collect(), address: null, coupon: $coupon
        ));

        $this->assertSame(800.0, $context->discount);
    }
}
