<?php

namespace App\Models;

use App\Models\Scopes\VendorOwnerScope;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $guarded = [];

    // Relations
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    // Local scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeFromVerifiedVendor(Builder $query): Builder
    {
        return $query->whereHas('vendor', fn ($q) => $q->where('is_verified', true));
    }

    public function scopeFromUnVerifiedVerdor(Builder $query): Builder
    {
        return $query->whereHas('vendor', fn ($q) => $q->where('is_verified', false));
    }

    public function scopeMinPrice(Builder $query, float $min): Builder
    {
        return $query->where('price', '>=', $min);
    }

    public function scopeMaxPrice(Builder $query, float $max): Builder
    {
        return $query->where('price', '<=', $max);
    }

    public function scopePriceBetween(Builder $query, float $min, float $max): Builder
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    public function scopeForCurrentVendor(Builder $query): Builder
    {
        $vendor = auth()->user()?->vendor;

        return $vendor
            ? $query->where('vendor_id', $vendor->id)
            : $query->whereRaw('1 = 0');   // no vendor → no rows (safer than returning all)
    }

    public function ratingAverage(): float
    {
        return $this->reviews->avg('rating');
    }

    public function reviewsCount(): int
    {
        return $this->reviews()->count();
    }

    // Global Scope
    //    protected static function booted()
    //    {
    //        static::addGlobalScope(new VendorOwnerScope()) ;
    //    }

    // Bypass The Global Scope
    protected static function allVendors(Builder $query): Builder
    {
        return $query->withOutGlobalScope(VendorOwnerScope::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (Product $product) {
            $product->reviews()->delete();
        });
    }
}
