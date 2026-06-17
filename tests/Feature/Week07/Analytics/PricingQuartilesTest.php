<?php

namespace Tests\Feature\Week07\Analytics;

use App\Models\Product;
use App\Models\Vendor;
use App\Repositories\AnalyticsRepository;

class PricingQuartilesTest extends AnalyticsTestCase
{
    public function test_it_splits_a_vendors_products_into_4_price_quartiles(): void
    {
        $vendor = Vendor::factory()->create();

        $prices = [10, 20, 30, 40, 50, 60, 70, 80];   // 8 products, 4 quartiles => 2 per quartile
        foreach ($prices as $p) {
            Product::factory()->active()->for($vendor)->create(['price' => $p]);
        }

        $res = app(AnalyticsRepository::class)->pricingQuartiles($vendor->id);

        $this->assertCount(8, $res);

        $byQuartile = $res->groupBy('price_quartile');
        $this->assertCount(2, $byQuartile[1]);    // cheapest
        $this->assertCount(2, $byQuartile[4]);    // priciest

        // First quartile should be the two cheapest.
        $q1Prices = $byQuartile[1]->pluck('price')->map(fn ($p) => (float) $p)->all();
        $this->assertEquals([10.0, 20.0], $q1Prices);
    }
}
