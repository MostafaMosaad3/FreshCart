<?php

namespace Tests\Feature\Week07\Cache;

use App\Models\Vendor;
use App\Repositories\AnalyticsRepositoryContract;
use Illuminate\Support\Facades\DB;

class VendorHealthCacheTest extends CacheTestCase
{
    public function test_it_returns_the_same_data_on_second_call_even_when_orders_change(): void
    {
        $vendor = Vendor::factory()->create();
        $variant = $this->variantForVendor($vendor);

        // Three delivered sales of 100 each inside the recent (<= 30d) window = 300.
        $this->sale($variant, 100, now()->subDays(2));
        $this->sale($variant, 100, now()->subDays(3));
        $this->sale($variant, 100, now()->subDays(4));

        $repo = app(AnalyticsRepositoryContract::class);

        $first = $repo->vendorHealthReport($vendor->id);
        $this->assertSame(300.0, (float) $first->recent_sales);

        // Wipe the underlying sales via raw SQL — bypasses the observer, so the
        // cached value should still be served on the next call (the cached window).
        DB::table('order_items')->update(['line_total' => 0]);

        $second = $repo->vendorHealthReport($vendor->id);
        $this->assertSame(300.0, (float) $second->recent_sales);   // served from cache
    }
}
