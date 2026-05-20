<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\States\Orders\OrderState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'placed_at' => 'datetime',

        'status' => OrderStatus::class,
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function currentState(): OrderState
    {
        return $this->status->currentState();
    }

    public function markAsPaid(): void
    {
        $this->currentState()->markAsPaid($this);
    }

    public function markAsShipped(?string $trackingNumber = null): void
    {
        $this->currentState()->markAsShipped($this, $trackingNumber);
    }

    public function markAsDelivered(): void
    {
        $this->currentState()->markAsDelivered($this);
    }

    public function cancel(?string $reason = null): void
    {
        $this->currentState()->cancel($this, $reason);
    }
}
