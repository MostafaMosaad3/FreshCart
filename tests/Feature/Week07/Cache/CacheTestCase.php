<?php

namespace Tests\Feature\Week07\Cache;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Vendor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Week07\Analytics\AnalyticsTestCase;

/**
 * Base case for the W7D4 Redis-caching tests.
 *
 * The cached analytics queries use MySQL-only window functions / CTEs, so we
 * inherit the dedicated `mysql_testing` connection + per-test transaction from
 * AnalyticsTestCase. The cache itself uses the `array` store in tests
 * (see phpunit.xml), flushed between cases so keys never leak across tests.
 *
 * FreshCart orders carry no vendor_id: a vendor's sales are reached through
 * order_items -> product_variants -> products.vendor_id, so we build sales the
 * same way the Day 2/3 analytics tests do.
 */
abstract class CacheTestCase extends AnalyticsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    protected function variantForVendor(Vendor $vendor): ProductVariant
    {
        return Product::factory()->active()->for($vendor)->create()->variants()->firstOrFail();
    }

    protected function sale(
        ProductVariant $variant,
        float $amount,
        Carbon $date,
        OrderStatus $status = OrderStatus::Delivered,
    ): Order {
        $order = Order::factory()->create([
            'status' => $status,
            'total' => $amount,
            'created_at' => $date,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => $amount,
            'line_total' => $amount,
        ]);

        return $order;
    }
}
