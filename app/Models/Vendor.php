<?php

namespace App\Models;

use Database\Factories\VendorFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Vendor extends Model
{
    /** @use HasFactory<VendorFactory> */
    use HasFactory;

    protected $guarded = [];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    // Local Scopes
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    public function scopeIsVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    public function scopeIsNotVerified(Builder $query): Builder
    {
        return $query->where('is_verified', false);
    }

    public function scopeWithProducts(Builder $query): Builder
    {
        return $query->has('products');
    }

    public function ratingAverage(): ?float
    {
        return $this->reviews()->avg('rating');
    }

    public function reviewsCount(): int
    {
        return $this->reviews()->count();
    }

    protected static function booted(): void
    {
        static::deleting(function (Vendor $vendor) {
            $vendor->reviews()->delete();
        });
    }
}
