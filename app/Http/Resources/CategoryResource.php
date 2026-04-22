<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'name'           => $this->name,
            'slug'           => $this->slug,
            'parent'         => new CategoryResource($this->whenLoaded('parent')),
            'children_count' => $this->whenHas('children_count'),
            'products_count' => $this->whenHas('products_count'),
        ];
    }
}
