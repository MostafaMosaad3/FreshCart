<?php

namespace Tests\Feature\Week07\Analytics;

use App\Models\Product;
use App\Models\Review;
use App\Repositories\AnalyticsRepository;

class RatingTrendTest extends AnalyticsTestCase
{
    public function test_it_computes_month_over_month_rating_change_per_product(): void
    {
        $product = Product::factory()->create();

        // Jan: avg 5.0 (two reviews, distinct users via the factory).
        Review::factory()->count(2)->forProduct($product)->create([
            'rating' => 5, 'created_at' => '2026-01-10',
        ]);

        // Feb: avg 3.0
        Review::factory()->count(2)->forProduct($product)->create([
            'rating' => 3, 'created_at' => '2026-02-10',
        ]);

        $res = app(AnalyticsRepository::class)->ratingTrend('product', $product->id, 6);

        $jan = $res->firstWhere('month', '2026-01');
        $feb = $res->firstWhere('month', '2026-02');

        $this->assertSame(5.0, (float) $jan->rating_avg);
        $this->assertNull($jan->prev_rating);          // first month, no previous
        $this->assertSame(3.0, (float) $feb->rating_avg);
        $this->assertSame(5.0, (float) $feb->prev_rating);
        $this->assertSame(-2.0, (float) $feb->delta);
    }
}
