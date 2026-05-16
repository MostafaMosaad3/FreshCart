<?php

namespace Tests\Feature\Pricing;

use App\Pricing\Decorators\ShippingDecorator;
use App\Pricing\PriceCalculatorInterface;
use App\Pricing\PriceContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShippingDecoratorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('commerce.shipping.free_threshold', 2000);
        config()->set('commerce.shipping.flat_rate', 50);
    }

    private function stubNext(float $subtotal): PriceCalculatorInterface
    {
        return new class($subtotal) implements PriceCalculatorInterface {
            public function __construct(private float $subtotal) {}
            public function calculate(PriceContext $context): \App\Pricing\PriceContext
            {
                $context->subtotal = $this->subtotal;
                return $context;
            }
        };
    }

    public function test_charges_flat_rate_below_threshold(): void
    {
        $shipping = new ShippingDecorator($this->stubNext(1500.0));
        $context = $shipping->calculate(new PriceContext(items: collect(), address: null));

        $this->assertSame(50.0, $context->shipping);
    }

    public function test_is_free_at_threshold(): void
    {
        $shipping = new ShippingDecorator($this->stubNext(2000.0));
        $context = $shipping->calculate(new PriceContext(items: collect(), address: null));

        $this->assertSame(0.0, $context->shipping);
    }

    public function test_is_free_above_threshold(): void
    {
        $shipping = new ShippingDecorator($this->stubNext(5000.0));
        $context = $shipping->calculate(new PriceContext(items: collect(), address: null));

        $this->assertSame(0.0, $context->shipping);
    }
}
