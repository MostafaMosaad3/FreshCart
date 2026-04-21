<?php

namespace App\Models;

use App\Models\Scopes\VendorOwnerScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Attributes\Scope;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
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
        return $query->whereHas('vendor', fn($q) => $q->where('is_verified', true));
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


    // Global Scope
    protected static function booted()
    {
        static::addGlobalScope(new VendorOwnerScope()) ;
    }

    // Bypass The Global Scope
    protected static function allVendors(Builder $query): Builder
    {
        return $query->withOutGlobalScope(VendorOwnerScope::class);
    }




}
