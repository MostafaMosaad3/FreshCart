<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Admins moderate every review through the Filament panel; everyone else
     * falls through to the per-ability checks below.
     */
    public function before(?User $user, string $ability): ?bool
    {
        return $user?->isAdmin() ? true : null;
    }

    /**
     * Determine whether the user can delete the review.
     *
     * The author can delete their own review; admins can delete any.
     */
    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id || $user->isAdmin();
    }
}
