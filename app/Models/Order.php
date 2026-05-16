<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $guarded = [ ];

    protected $casts = [
        'subtotal'  => 'decimal:2',
        'tax'       => 'decimal:2',
        'shipping'  => 'decimal:2',
        'discount'  => 'decimal:2',
        'total'     => 'decimal:2',
        'placed_at' => 'datetime',
    ];

    public function user() :BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items() :HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
