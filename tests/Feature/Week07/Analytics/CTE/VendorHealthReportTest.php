<?php

namespace Tests\Feature\Week07\Analytics\CTE;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Vendor;
use App\Repositories\AnalyticsRepository;
use Illuminate\Support\Carbon;
use Tests\Feature\Week07\Analytics\AnalyticsTestCase;

class VendorHealthReportTest extends AnalyticsTestCase
{
    public function test_it_calculates_sales_growth_from_recent_vs_prior_window(): void
    {
        $vendor = Vendor::factory()->create();
        $variant = $this->variantForVendor($vendor);

        // Recent window (<= 30 days): 1500
        $this->sale($variant, 1000, now()->subDays(5));
        $this->sale($variant, 500, now()->subDays(10));

        // Prior window (30–60 days): 1000
        $this->sale($variant, 1000, now()->subDays(45));

        $report = app(AnalyticsRepository::class)->vendorHealthReport($vendor->id);

        $this->assertSame(1500.0, (float) $report->recent_sales);
        $this->assertSame(1000.0, (float) $report->prior_sales);
        $this->assertSame(50.0, (float) $report->sales_growth_pct);   // (1500-1000)/1000*100
    }

    public function test_a_new_vendor_with_no_prior_sales_has_null_growth(): void
    {
        $vendor = Vendor::factory()->create();
        $variant = $this->variantForVendor($vendor);

        $this->sale($variant, 800, now()->subDays(3));

        $report = app(AnalyticsRepository::class)->vendorHealthReport($vendor->id);

        $this->assertSame(800.0, (float) $report->recent_sales);
        $this->assertSame(0.0, (float) $report->prior_sales);
        $this->assertNull($report->sales_growth_pct);   // division by zero guarded -> NULL
    }

    public function test_it_computes_the_cancellation_rate(): void
    {
        $vendor = Vendor::factory()->create();
        $variant = $this->variantForVendor($vendor);

        // 3 delivered + 1 cancelled = 1/4 = 0.25
        $this->sale($variant, 100, now()->subDays(2), OrderStatus::Delivered);
        $this->sale($variant, 100, now()->subDays(3), OrderStatus::Delivered);
        $this->sale($variant, 100, now()->subDays(4), OrderStatus::Delivered);
        $this->sale($variant, 100, now()->subDays(5), OrderStatus::Cancelled);

        $report = app(AnalyticsRepository::class)->vendorHealthReport($vendor->id);

        $this->assertSame(0.25, (float) $report->cancel_rate);
    }

    public function test_it_lists_the_top_3_products_as_a_string_ordered_by_rating(): void
    {
        $vendor = Vendor::factory()->create();

        Product::factory()->active()->for($vendor)->create(['name' => 'Apple', 'rating_avg' => 5.0]);
        Product::factory()->active()->for($vendor)->create(['name' => 'Banana', 'rating_avg' => 4.0]);
        Product::factory()->active()->for($vendor)->create(['name' => 'Cherry', 'rating_avg' => 3.0]);
        Product::factory()->active()->for($vendor)->create(['name' => 'Date', 'rating_avg' => 2.0]);

        $report = app(AnalyticsRepository::class)->vendorHealthReport($vendor->id);

        $this->assertSame('Apple, Banana, Cherry', $report->top_products);
    }

    public function test_it_aggregates_the_recent_vendor_rating(): void
    {
        $vendor = Vendor::factory()->create();

        Review::factory()->forVendor($vendor)->create(['rating' => 4, 'created_at' => now()->subDays(5)]);
        Review::factory()->forVendor($vendor)->create(['rating' => 2, 'created_at' => now()->subDays(6)]);

        $report = app(AnalyticsRepository::class)->vendorHealthReport($vendor->id);

        $this->assertSame(3.0, (float) $report->recent_rating);   // (4 + 2) / 2
        $this->assertSame(2, (int) $report->recent_reviews);
    }

    private function variantForVendor(Vendor $vendor): ProductVariant
    {
        $product = Product::factory()->active()->for($vendor)->create();

        return $product->variants()->firstOrFail();
    }

    private function sale(
        ProductVariant $variant,
        float $amount,
        Carbon $date,
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
