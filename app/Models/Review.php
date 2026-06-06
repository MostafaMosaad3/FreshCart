<?php

namespace App\Models;

use App\Observers\ReviewAggregateObserver;
use App\Observers\ReviewNotificationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[ObservedBy([
    ReviewAggregateObserver::class,
    ReviewNotificationObserver::class,
])]
class Review extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeHighRated($query, int $min = 4)
    {
        return $query->where('rating', '>=', $min);
    }
}
