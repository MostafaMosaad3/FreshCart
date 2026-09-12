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

        $first = now()->subMonth()->startOfMonth()->addDays(9);
        $second = now()->startOfMonth();

        // First month: avg 5.0 (two reviews, distinct users via the factory).
        Review::factory()->count(2)->forProduct($product)->create([
            'rating' => 5, 'created_at' => $first->toDateString(),
        ]);

        // Second month: avg 3.0
        Review::factory()->count(2)->forProduct($product)->create([
            'rating' => 3, 'created_at' => $second->toDateString(),
        ]);

        $res = app(AnalyticsRepository::class)->ratingTrend('product', $product->id, 6);

        $jan = $res->firstWhere('month', $first->format('Y-m'));
        $feb = $res->firstWhere('month', $second->format('Y-m'));

        $this->assertSame(5.0, (float) $jan->rating_avg);
        $this->assertNull($jan->prev_rating);          // first month, no previous
        $this->assertSame(3.0, (float) $feb->rating_avg);
        $this->assertSame(5.0, (float) $feb->prev_rating);
        $this->assertSame(-2.0, (float) $feb->delta);
    }
}
