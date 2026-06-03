<?php

namespace Tests\Feature\Pricing;

use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Models\User;
use App\Pricing\PriceCalculatorInterface;
use App\Pricing\PriceContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FullChainTest extends TestCase
{
    use RefreshDatabase;

    public function test_computes_tax_shipping_and_no_discount_correctly(): void
    {
        config()->set('commerce.tax.default_rate', 0.14);
        config()->set('commerce.shipping.free_threshold', 2000);
        config()->set('commerce.shipping.flat_rate', 50);

        $variant = ProductVariant::factory()->create(['price' => 100]);
        $user = User::factory()->customer()->create();

        $cart = $user->cart;
        $cart->items()->create([
            'variant_id' => $variant->id,
            'quantity' => 10,
            'unit_price' => 100,
        ]);

        $ctx = app(PriceCalculatorInterface::class)->calculate(new PriceContext(
            items: $cart->items,
            address: null,
            coupon: null,
        ));

        $this->assertSame(1000.0, $ctx->subtotal);
        $this->assertSame(140.0, $ctx->tax);
        $this->assertSame(50.0, $ctx->shipping);
        $this->assertSame(0.0, $ctx->discount);
        $this->assertSame(1190.0, $ctx->total());

    }

    public function test_applies_free_shipping_above_threshold(): void
    {
        config()->set('commerce.shipping.free_threshold', 2000);
        config()->set('commerce.shipping.flat_rate', 50);

        $variant = ProductVariant::factory()->create(['price' => 100]);
        $user = User::factory()->customer()->create();

        $cart = $user->cart;
        $cart->items()->create([
            'variant_id' => $variant->id,
            'quantity' => 25,   // subtotal = 2500
            'unit_price' => 100,
        ]);

        $ctx = app(PriceCalculatorInterface::class)->calculate(new PriceContext(
            items: $cart->items,
            address: null,
            coupon: null,
        ));

        $this->assertSame(0.0, $ctx->shipping);
    }

    public function test_applies_a_percent_coupon(): void
    {
        $variant = ProductVariant::factory()->create(['price' => 100]);
        $user = User::factory()->customer()->create();
        $coupon = Coupon::factory()->create(['strategy' => 'percentage', 'config' => ['value' => 10]]);

        $cart = $user->cart;
        $cart->items()->create([
            'variant_id' => $variant->id,
            'quantity' => 10,
            'unit_price' => 100,
        ]);

        $ctx = app(PriceCalculatorInterface::class)->calculate(new PriceContext(
            items: $cart->items,
            address: null,
            coupon: $coupon,
        ));

        $this->assertSame(100.0, $ctx->discount);
    }
}
