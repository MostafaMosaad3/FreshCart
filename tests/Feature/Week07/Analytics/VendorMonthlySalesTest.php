<?php

namespace Tests\Feature\Week07\Analytics;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Vendor;
use App\Repositories\AnalyticsRepository;

class VendorMonthlySalesTest extends AnalyticsTestCase
{
    public function test_it_returns_monthly_sales_with_correct_running_total(): void
    {
        $vendor = Vendor::factory()->create();
        $variant = $this->variantForVendor($vendor);

        $this->sale($variant, 1000, '2026-01-15');
        $this->sale($variant, 500, '2026-02-15');
        $this->sale($variant, 200, '2026-03-15');

        $res = app(AnalyticsRepository::class)->vendorMonthlySales($vendor->id, 6);

        $this->assertCount(3, $res);
        $this->assertSame(1000.0, (float) $res[0]->monthly_sales);
        $this->assertSame(1000.0, (float) $res[0]->cumulative_sales);
        $this->assertSame(500.0, (float) $res[1]->monthly_sales);
        $this->assertSame(1500.0, (float) $res[1]->cumulative_sales);
        $this->assertSame(200.0, (float) $res[2]->monthly_sales);
        $this->assertSame(1700.0, (float) $res[2]->cumulative_sales);
    }

    public function test_it_only_counts_delivered_orders(): void
    {
        $vendor = Vendor::factory()->create();
        $variant = $this->variantForVendor($vendor);

        $this->sale($variant, 100, now()->toDateString(), OrderStatus::Delivered);
        $this->sale($variant, 1000, now()->toDateString(), OrderStatus::Cancelled);

        $res = app(AnalyticsRepository::class)->vendorMonthlySales($vendor->id);

        $this->assertSame(100.0, (float) $res->sum('monthly_sales'));
    }

    private function variantForVendor(Vendor $vendor): ProductVariant
    {
        $product = Product::factory()->active()->for($vendor)->create();

        return $product->variants()->firstOrFail();
    }

    private function sale(
        ProductVariant $variant,
        float $amount,
        string $date,
        OrderStatus $status = OrderStatus::Delivered,
    ): void {
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
    }
}
