<?php

namespace Tests\Feature\Pricing;

use App\Pricing\Decorators\TaxDecorator;
use App\Pricing\PriceCalculatorInterface;
use App\Pricing\PriceContext;
use Tests\TestCase;

class TaxDecoratorTest extends TestCase
{
    private function stubNext(float $subtotal = 0.0): PriceCalculatorInterface
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

    public function test_adds_tax_as_a_percentage_of_subtotal(): void
    {
        $tax = new TaxDecorator($this->stubNext(1000.0), 0.14);
        $context = $tax->calculate(new PriceContext(items: collect(), address: null));

        $this->assertSame(1000.0, $context->subtotal);
        $this->assertSame(140.0, $context->tax);
    }

    public function test_tax_is_zero_when_subtotal_is_zero(): void
    {
        $tax = new TaxDecorator($this->stubNext(0.0), 0.14);
        $context = $tax->calculate(new PriceContext(items: collect(), address: null));

        $this->assertSame(0.0, $context->tax);
    }

    public function test_supports_zero_rate(): void
    {
        $tax = new TaxDecorator($this->stubNext(1000.0), 0.0);
        $context = $tax->calculate(new PriceContext(items: collect(), address: null));

        $this->assertSame(0.0, $context->tax);
    }
}
