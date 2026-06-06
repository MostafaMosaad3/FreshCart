<?php

namespace App\Observers;

use App\Models\Review;
use Illuminate\Support\Facades\DB;

class ReviewAggregateObserver
{
    public function created(Review $review): void
    {
        $this->recalculate($review);
    }

    public function updated(Review $review): void
    {
        if ($review->wasChanged('rating')) {
            $this->recalculate($review);
        }
    }

    public function deleted(Review $review): void
    {
        $this->recalculate($review);
    }

    private function recalculate(Review $review): void
    {
        $parent = $review->reviewable;
        if (! $parent) {
            return;
        }

        DB::transaction(function () use ($parent) {
            $locked = $parent->newQueryWithoutScopes()
                ->whereKey($parent->getKey())
                ->lockForUpdate()
                ->first();

            if (! $locked) {
                return;
            }

            $locked->forceFill([
                'rating_avg' => $locked->reviews()->avg('rating') ?? 0,
                'reviews_count' => $locked->reviews()->count(),
            ])->saveQuietly();
        });
    }
}
