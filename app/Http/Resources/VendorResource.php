<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'store_name'     => $this->store_name,
            'slug'           => $this->slug,
            'verified'       => (bool) $this->is_verified,
            'products_count' => $this->whenHas('products_count'),
        ];
    }
}
