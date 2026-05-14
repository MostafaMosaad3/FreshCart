<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'order_number' => $this->order_number,
            'status'       => $this->status,
            'subtotal'     => $this->subtotal,
            'tax'          => $this->tax,
            'shipping'     => $this->shipping,
            'discount'     => $this->discount,
            'total'        => $this->total,
            'placed_at'    => $this->placed_at?->toIso8601String(),
            'items'        => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
