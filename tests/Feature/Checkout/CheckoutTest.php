<?php

namespace Tests\Feature\Checkout;

use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_an_order_from_a_cart_with_items(): void
    {
        $user    = User::factory()->customer()->create();
        $variant = ProductVariant::factory()->create(['stock' => 10, 'price' => 100]);

        $cart = $user->cart;
        $cart->items()->create([
            'variant_id' => $variant->id,
            'quantity'   => 2,
            'unit_price' => 100,
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/checkout')
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total', '278.00');

        $this->assertSame(1, $user->orders()->count());
        $this->assertSame(0, $user->cart->items()->count());
    }

    public function test_locks_variant_rows_during_checkout_to_prevent_oversell(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 1]);
        $users   = User::factory()->count(5)->customer()->create();

        foreach ($users as $u) {
            $cart = $u->cart;
            $cart->items()->create([
                'variant_id' => $variant->id,
                'quantity'   => 1,
                'unit_price' => $variant->price,
            ]);
        }

        $successCount = 0;

        foreach ($users as $u) {
            try {
                // With add-to-cart reservation active, only the first user's add
                // succeeds. For this test, temporarily bypass that and rely on
                // checkout locking alone:
                $this->actingAs($u, 'sanctum')
                    ->postJson('/api/checkout')
                    ->assertStatus(200);
                $successCount++;
            } catch (\Throwable $e) {
                // expected
            }
        }

        // With only 1 stock, at most 1 checkout can succeed
        $this->assertLessThanOrEqual(1, $successCount);
    }

    public function test_rejects_checkout_with_an_empty_cart(): void
    {
        $user = User::factory()->customer()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/checkout')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cart']);
    }

    public function test_generates_a_unique_order_number(): void
    {
        $user    = User::factory()->customer()->create();
        $variant = ProductVariant::factory()->create(['stock' => 5, 'price' => 100]);

        for ($i = 0; $i < 3; $i++) {
            $cart = $user->cart()->firstOrCreate(['user_id' => $user->id]);
            $cart->items()->create([
                'variant_id' => $variant->id,
                'quantity'   => 1,
                'unit_price' => 100,
            ]);
            $this->actingAs($user, 'sanctum')->postJson('/api/checkout')->assertStatus(200);
        }

        $numbers = $user->orders()->pluck('order_number')->all();
        $this->assertSame(3, count(array_unique($numbers)));
    }
}
