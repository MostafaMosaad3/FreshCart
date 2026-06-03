<?php

namespace Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutUsesGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_the_configured_gateway_during_checkout(): void
    {
        $spy = new class implements PaymentGatewayInterface
        {
            public bool $wasCalled = false;

            public function charge(int $amountInCents, string $currency = 'EGP'): array
            {
                $this->wasCalled = true;

                return ['success' => true, 'transaction_id' => 'SPY-'.uniqid()];
            }

            public function refund(string $transactionId, int $amountInCents): array
            {
                return ['success' => true, 'refund_id' => 'R-'.uniqid()];
            }
        };
        $this->app->instance(PaymentGatewayInterface::class, $spy);

        $variant = ProductVariant::factory()->create(['stock' => 10, 'price' => 100]);
        $user = User::factory()->customer()->create();
        $user->cart->items()->create([
            'variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 100,
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/checkout')
            ->assertStatus(200);

        $this->assertTrue($spy->wasCalled);
    }

    public function test_fails_checkout_when_gateway_declines(): void
    {
        $decliner = new class implements PaymentGatewayInterface
        {
            public function charge(int $amountInCents, string $currency = 'EGP'): array
            {
                return ['success' => false, 'reason' => 'Insufficient funds'];
            }

            public function refund(string $transactionId, int $amountInCents): array
            {
                return ['success' => true];
            }
        };
        $this->app->instance(PaymentGatewayInterface::class, $decliner);

        $variant = ProductVariant::factory()->create(['stock' => 10, 'price' => 100]);
        $user = User::factory()->customer()->create();
        $user->cart->items()->create([
            'variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 100,
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/checkout')
            ->assertStatus(402);

        $this->assertSame(0, $user->orders()->count());

    }
}
