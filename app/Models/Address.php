<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'label', 'name', 'phone',
        'line1', 'line2', 'city', 'state',
        'country_code', 'postal_code', 'is_default',
    ];

    protected $casts = [
        'is_default' => 'bool',
    ];
    protected $guarded = [];

    public function user() :belongsTo
    {
        return $this->belongsTo(User::class);
    }
}
