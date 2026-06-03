<?php

namespace App\Models;

use App\Pricing\Discounts\CouponValidationResult;
use Database\Factories\CouponFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    /** @use HasFactory<CouponFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'config' => 'array',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'first_order_only' => 'boolean',
        'min_subtotal' => 'decimal:2',
    ];

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>=', now()));
    }

    public function scopeAvailableFor(Builder $q, string $code): Builder
    {
        return $q->where('code', $code)->active();
    }

    public function isValidForCart(User $user, Cart $cart): CouponValidationResult
    {
        if (! $this->is_active) {
            return new CouponValidationResult(false, 'inactive');
        }
        if ($this->expires_at?->isPast()) {
            return new CouponValidationResult(false, 'expired');
        }
        if ($this->starts_at?->isFuture()) {
            return new CouponValidationResult(false, 'not_started');
        }
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return new CouponValidationResult(false, 'max_uses_reached');
        }
        if ($this->min_subtotal && $cart->subtotal < $this->min_subtotal) {
            return new CouponValidationResult(false, 'below_min_subtotal');
        }
        if ($this->first_order_only
            && $user->orders()->whereIn('status', ['paid', 'shipped', 'delivered'])->exists()) {
            return new CouponValidationResult(false, 'not_first_order');
        }
        if ($this->max_per_user !== null) {
            $used = $this->redemptions()->where('user_id', $user->id)->count();
            if ($used >= $this->max_per_user) {
                return new CouponValidationResult(false, 'user_limit_reached');
            }
        }

        return new CouponValidationResult(true, null);
    }
}
