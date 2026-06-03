<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $guarded = [];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    // Accessors
    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum(fn ($item) => $item->unit_price * $item->quantity);
    }

    public function totalItems(): int
    {
        return $this->items()->sum('quantity');
    }

    public function totalPrice(): string
    {
        return number_format(
            $this->items->sum(fn ($item) => $item->unit_price * $item->quantity),
        );
    }
}
