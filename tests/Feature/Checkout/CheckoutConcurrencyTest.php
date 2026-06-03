<?php

namespace Tests\Feature\Checkout;

use App\Http\Controllers\Api\CheckoutController;
use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_prevents_overselling_under_concurrent_checkouts(): void
    {
        $variant = ProductVariant::factory()->create(['stock' => 5, 'price' => 100]);

        $users = User::factory()->count(10)->customer()->create();
        foreach ($users as $user) {
            $cart = $user->cart;
            $cart->items()->create([
                'variant_id' => $variant->id,
                'quantity' => 1,
                'unit_price' => $variant->price,
            ]);
        }

        // PHPUnit can't truly parallelize within one test — we assert the INVARIANT:
        // total successful orders ≤ initial stock, no matter the ordering.
        $controller = app(CheckoutController::class);
        $successCount = 0;

        foreach ($users as $user) {
            try {
                $request = PlaceOrderRequest::create('/api/checkout', 'POST');
                $request->setUserResolver(fn () => $user);
                $controller->place($request);
                $successCount++;
            } catch (\Throwable $e) {
                // expected for users whose checkout exceeds remaining stock
            }
        }

        $this->assertLessThanOrEqual(5, $successCount);
        $this->assertGreaterThanOrEqual(0, $variant->fresh()->stock);
    }
}
