<?php

namespace App\Models;

use App\Observers\ProductObserver;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Laravel\Scout\Searchable;

#[ObservedBy(ProductObserver::class)]

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use Searchable;

    protected $guarded = [];

    protected $casts = [
        'rating_avg' => 'decimal:2',
        'reviews_count' => 'integer',
        'tags' => 'array',
    ];

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

    public function scopeFromUnVerifiedVendor(Builder $query): Builder
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

    //    public function ratingAverage(): float
    //    {
    //        return $this->reviews->avg('rating');
    //    }

    public function ratingAverage(): float
    {
        return (float) $this->rating_avg;
    }

    public function reviewsCount(): int
    {
        return $this->reviews()->count();
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category_names' => $this->categories->pluck('name')->all(),
            'category_ids' => $this->categories->pluck('id')->all(),
            'vendor_name' => $this->vendor->store_name,
            'vendor_id' => (int) $this->vendor_id,
            'price' => (float) $this->price,
            'rating_avg' => (float) $this->rating_avg,
            'in_stock' => $this->stock > 0,
            'tags' => $this->tags ?? [],
            'created_at' => $this->created_at->timestamp,
        ];
    }

    public function makeSearchableUsing(Collection $models): Collection
    {
        return $models->load('categories', 'vendor');
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === 'active';
    }

    public function searchableAs(): string
    {
        return 'products_'.app()->environment();
    }

    // Global Scope
    //    protected static function booted()
    //    {
    //        static::addGlobalScope(new VendorOwnerScope()) ;
    //    }

    // Bypass The Global Scope
    //    protected static function allVendors(Builder $query): Builder
    //    {
    //        return $query->withOutGlobalScope(VendorOwnerScope::class);
    //    }

    //    protected static function booted(): void
    //    {
    //        static::deleting(function (Product $product) {
    //            $product->reviews()->delete();
    //        });
    //    }
}
