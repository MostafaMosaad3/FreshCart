<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ,
            'variant_id' => $this->variant?->id ,
            'variant_sku' => $this->variant?->sku ,
            'variant_name' => $this->variant?->name ,
            'variant_attributes' => $this->variant?->attributes ,
            'variant_price' => $this->variant?->price ,
            'product_id' => $this->variant?->product?->id  ,
            'product_slug' => $this->variant?->product?->slug ,
            'product_name' => $this->variant?->product?->name  ,
            'quantity' => $this->quantity ,
            'unit_price' => $this->unit_price ,
            'line_total' => $this->stock
        ];

    }
}
