<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductSearchRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;

class SearchController extends Controller
{
    public function products(ProductSearchRequest $request)
    {
        $q = $request->input('q', '');

        $builder = Product::search($q);

        if ($request->filled('vendor_id')) {
            $builder->where('vendor_id', $request->integer('vendor_id'));
        }

        if ($request->filled('category_ids')) {
            $builder->whereIn('category_ids', $request->input('category_ids'));
        }

        if ($request->filled('price_min')) {
            $builder->where('price', '>=', (float) $request->input('price_min'));
        }

        if ($request->filled('price_max')) {
            $builder->where('price', '<=', (float) $request->input('price_max'));
        }

        if ($request->filled('rating_min')) {
            $builder->where('rating_avg', '>=', (float) $request->input('rating_min'));
        }

        // Default to in-stock only; allow opt-out
        $inStock = $request->has('in_stock') ? $request->boolean('in_stock') : true;
        $builder->where('in_stock', $inStock);

        match ($request->input('sort')) {
            'price_asc' => $builder->orderBy('price', 'asc'),
            'price_desc' => $builder->orderBy('price', 'desc'),
            'rating_desc' => $builder->orderBy('rating_avg', 'desc'),
            'newest' => $builder->orderBy('created_at', 'desc'),
            default => null,   // relevance
        };

        $paginated = $builder->paginate(20);

        return ProductResource::collection($paginated)->additional([
            'meta' => [
                'query' => $q,
            ],
        ]);
    }

    public function facets(ProductSearchRequest $request)
    {
        $q = $request->input('q', '');

        $raw = Product::search($q)->raw([
            'facets' => ['category_names', 'vendor_name'],
            'filter' => 'in_stock = true',
            'limit' => 0,   // we only want counts, not hits
        ]);

        return [
            'facets' => $raw['facetDistribution'] ?? [],
        ];
    }
}
