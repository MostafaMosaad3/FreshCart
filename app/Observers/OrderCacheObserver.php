<?php

namespace App\Observers;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderCacheObserver
{
    public function updated(Order $order): void
    {
        // Only a status change moves the delivered/cancelled aggregates.
        if (! $order->wasChanged('status')) {
            return;
        }

        $this->flush($order);
    }

    public function created(Order $order): void
    {
        // A new order affects vendor counts even before delivery.
        $this->flush($order);
    }

    private function flush(Order $order): void
    {
        Cache::forget('analytics.category.performance_tree.v1');
        Cache::forget("analytics.customer_insights.v1.{$order->user_id}");

        // FreshCart orders have no vendor_id: a vendor is reached through
        // order_items -> product_variants -> products.vendor_id, and a single
        // order may span several vendors.
        foreach ($this->vendorIdsFor($order) as $vendorId) {
            Cache::forget("analytics.vendor_health.v1.vendor={$vendorId}");
            Cache::forget("analytics.vendor_monthly_sales.v1.vendor={$vendorId}.months=12");
        }
    }

    /**
     * @return array<int, int>
     */
    private function vendorIdsFor(Order $order): array
    {
        return DB::table('order_items as oi')
            ->join('product_variants as pv', 'pv.id', '=', 'oi.variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->where('oi.order_id', $order->id)
            ->distinct()
            ->pluck('p.vendor_id')
            ->all();
    }
}
