<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    /** @use HasFactory<\Database\Factories\ProductVariantFactory> */
    use HasFactory;

    protected $guarded = [] ;

    protected $casts = [
        'attributes' => 'array',
        'prices' => 'decimal:2',
    ] ;

    public function product() : BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
