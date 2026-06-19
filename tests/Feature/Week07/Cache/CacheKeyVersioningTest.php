<?php

namespace Tests\Feature\Week07\Cache;

use App\Models\Vendor;
use App\Repositories\AnalyticsRepositoryContract;
use Illuminate\Support\Facades\Cache;

class CacheKeyVersioningTest extends CacheTestCase
{
    public function test_it_uses_versioned_keys_that_survive_a_code_refactor(): void
    {
        $vendor = Vendor::factory()->create();
        $variant = $this->variantForVendor($vendor);
        $this->sale($variant, 100, now()->subDays(2));

        $repo = app(AnalyticsRepositoryContract::class);
        $repo->vendorHealthReport($vendor->id);

        // Keys are namespaced + versioned: {domain}.{resource}.{version}.{params}
        $this->assertTrue(Cache::has("analytics.vendor_health.v1.vendor={$vendor->id}"));
    }
}
