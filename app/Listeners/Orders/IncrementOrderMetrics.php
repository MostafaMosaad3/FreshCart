<?php

namespace App\Listeners\Orders;

use Illuminate\Support\Facades\Cache;

class IncrementOrderMetrics
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $day = now()->format('Y-m-d');
        Cache::increment("analytics.daily_orders.{$day}");

        $vendorIds = $event->order->items
            ->pluck('variant.product.vendor_id')
            ->unique();

        foreach ($vendorIds as $vendorId) {
            Cache::increment("analytics.daily_orders.vendor.{$vendorId}.{$day}");
        }
    }
}
