<?php

namespace App\Observers;

use App\Models\Review;
use App\Models\User;
use App\Notifications\NewReviewNotification;

class ReviewNotificationObserver
{
    /**
     * Handle the Review "created" event.
     */
    public function created(Review $review): void
    {
        $owner = $this->ownerOf($review);

        if (! $owner || $owner->id === $review->user_id) {
            return;
        }

        $owner->notify(new NewReviewNotification($review));
    }

    /**
     * Handle the Review "updated" event.
     */
    public function updated(Review $review): void
    {
        //
    }

    /**
     * Handle the Review "deleted" event.
     */
    public function deleted(Review $review): void
    {
        //
    }

    /**
     * Handle the Review "restored" event.
     */
    public function restored(Review $review): void
    {
        //
    }

    /**
     * Handle the Review "force deleted" event.
     */
    public function forceDeleted(Review $review): void
    {
        //
    }

    private function ownerOf(Review $review): ?User
    {
        $target = $review->reviewable;
        if (! $target) {
            return null;
        }

        return match ($review->reviewable_type) {
            'vendor' => $target->user ,
            'product' => $target->vendor?->user ,
            default => null
        };

    }
}
