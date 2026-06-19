<?php

namespace App\Observers;

use App\Models\Review;
use Illuminate\Support\Facades\Cache;

class ReviewCacheObserver
{
    public function saved(Review $review): void
    {
        if ($review->reviewable_type === 'vendor') {
            Cache::forget("analytics.vendor_health.v1.{$review->reviewable_id}");
        }

        Cache::forget('analytics.top_products.v1.top_n=3');
        Cache::forget("analytics.rating_trend.v1.{$review->reviewable_type}.{$review->reviewable_id}.months=12");
    }

    public function deleted(Review $review): void
    {
        $this->saved($review);   // same logic
    }
}
