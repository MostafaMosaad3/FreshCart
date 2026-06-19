<?php

namespace Tests\Feature\Week07\Cache;

use App\Enums\OrderStatus;
use App\Models\Vendor;
use App\Repositories\AnalyticsRepositoryContract;

class CacheInvalidationTest extends CacheTestCase
{
    public function test_it_invalidates_vendor_health_cache_when_order_status_changes(): void
    {
        $vendor = Vendor::factory()->create();
        $variant = $this->variantForVendor($vendor);

        // A pending sale of 500 contributes nothing to recent (delivered) sales yet.
        $order = $this->sale($variant, 500, now()->subDays(2), OrderStatus::Pending);

        $repo = app(AnalyticsRepositoryContract::class);

        $first = $repo->vendorHealthReport($vendor->id);
        $this->assertSame(0.0, (float) $first->recent_sales);

        // Flip to delivered via Eloquent → fires OrderCacheObserver → should drop the cache.
        $order->update(['status' => OrderStatus::Delivered]);

        $second = $repo->vendorHealthReport($vendor->id);
        $this->assertSame(500.0, (float) $second->recent_sales);
    }
}
