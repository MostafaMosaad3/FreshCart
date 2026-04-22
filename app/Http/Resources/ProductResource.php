<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->Slug,
            'price' => (float)$this->price ,
            'currency' => 'EGP' ,
            'has_discount' => $this->compare_price !== null ,
            'discount_percentage' => $this->compare_price
                ? (int) round((1 - $this->price / $this->compare_price) * 100)
                : null,

            'status' => $this->status ,
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'created_at' => $this->created_at?->toISOString(),

            ];
    }
}
