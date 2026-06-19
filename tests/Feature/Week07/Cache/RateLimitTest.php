<?php

namespace Tests\Feature\Week07\Cache;

use App\Models\User;
use App\Models\Vendor;

class RateLimitTest extends CacheTestCase
{
    public function test_it_rate_limits_admin_analytics_to_30_per_minute(): void
    {
        $admin = User::factory()->admin()->create();
        $vendor = Vendor::factory()->create();

        $this->actingAs($admin, 'sanctum');

        // 30 requests succeed
        for ($i = 0; $i < 30; $i++) {
            $this->getJson("/api/admin/analytics/vendor-health/{$vendor->id}")
                ->assertOk();
        }

        // 31st is rate-limited
        $this->getJson("/api/admin/analytics/vendor-health/{$vendor->id}")
            ->assertStatus(429);
    }
}
