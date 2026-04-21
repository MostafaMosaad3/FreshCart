<?php

namespace App\Queries;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductFilter
{
    public function __construct(private Request $request)
    {
    }

    public function apply()
    {
        return Product::active()
            ->when($this->request->category, fn($q, $cat) =>
            $q->whereHas('categories', fn($c) => $c->where('slug', $cat))
            )
            ->when($this->request->filled('min_price'), fn($q) =>
            $q->minPrice($this->request->min_price)
            )
            ->when($this->request->filled('max_price'), fn($q) =>
            $q->maxPrice($this->request->max_price)
            )
            ->when($this->request->vendor, fn($q, $v) =>
            $q->whereHas('vendor', fn($vq) => $vq->where('slug', $v))
            )
            ->when($this->request->sort, fn($q, $sort) =>
            $q->orderBy($sort, $this->request->get('direction', 'asc'))
            );
    }
}
