<?php

namespace Tests\Feature\Coupons;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplyCouponEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_applies_a_valid_coupon(): void
    {
        $user = User::factory()->create();
        Coupon::factory()->create([
            'code' => 'WELCOME20',
            'strategy' => 'percentage',
            'config' => ['value' => 20],
        ]);

        $this->actingAs($user)
            ->postJson('/api/cart/coupon', ['code' => 'WELCOME20'])
            ->assertOk()
            ->assertJson(['success' => true, 'description' => '20% off']);

        $this->assertSame(
            'WELCOME20',
            $user->cart->coupon->code,
        );
    }

    public function test_rejects_an_unknown_code(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/api/cart/coupon', ['code' => 'NOPE'])
            ->assertStatus(422)
            ->assertJson(['error' => 'Invalid coupon']);
    }

    public function test_rejects_an_expired_coupon(): void
    {
        // The active() scope filters expired coupons out before lookup, so an
        // expired code is reported as "Invalid coupon" rather than found-but-expired.
        Coupon::factory()->create([
            'code' => 'OLD',
            'expires_at' => now()->subDay(),
        ]);

        $this->actingAs(User::factory()->create())
            ->postJson('/api/cart/coupon', ['code' => 'OLD'])
            ->assertStatus(422)
            ->assertJson(['error' => 'Invalid coupon']);
    }

    public function test_rejects_when_max_uses_reached(): void
    {
        Coupon::factory()->create([
            'code' => 'MAXED',
            'max_uses' => 1,
            'used_count' => 1,
        ]);

        $this->actingAs(User::factory()->create())
            ->postJson('/api/cart/coupon', ['code' => 'MAXED'])
            ->assertStatus(422)
            ->assertJson(['error' => 'max_uses_reached']);
    }

    public function test_removes_an_applied_coupon(): void
    {
        $user = User::factory()->create();
        $coupon = Coupon::factory()->create(['code' => 'DROPME']);

        $this->actingAs($user)
            ->postJson('/api/cart/coupon', ['code' => 'DROPME'])
            ->assertOk();

        $this->actingAs($user)
            ->deleteJson('/api/cart/coupon')
            ->assertNoContent();

        $this->assertNull($user->cart()->first()->coupon_id);
    }
}
