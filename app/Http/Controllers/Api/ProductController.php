<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('vendor');
        $user  = $request->user('sanctum');

        if ($user?->isAdmin()) {
            // admin sees everything
        } elseif ($user?->isVendor() && $user->vendor) {
            $query->where('vendor_id', $user->vendor->id);
        } else {
            $query->where('status', 'active')
                ->whereHas('vendor', fn($q) => $q->where('is_verified', true));
        }

        return ProductResource::collection($query->latest()->paginate(15));
    }

    public function show(Product $product)
    {
        $this->authorize('view', $product);

        $product->load(['vendor', 'categories']);

        return new ProductResource($product);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Product::class);

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'price'          => ['required', 'numeric', 'min:0'],
            'compare_price'  => ['nullable', 'numeric', 'gt:price'],
            'status'         => ['sometimes', 'in:active,draft,inactive'],
            'category_ids'   => ['nullable', 'array'],
            'category_ids.*' => ['exists:categories,id'],
        ]);

        $categoryIds         = $data['category_ids'] ?? [];
        $data['slug']        = Str::slug($data['name']) . '-' . Str::random(6);
        $data['description'] = $data['description'] ?? '';

        unset($data['category_ids']);

        $product = $request->user()->vendor->products()->create($data);

        if (! empty($categoryIds)) {
            $product->categories()->sync($categoryIds);
        }

        $product->load(['vendor', 'categories']);

        return (new ProductResource($product))->response()->setStatusCode(201);
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $data = $request->validate([
            'name'        => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'price'       => ['sometimes', 'numeric', 'min:0'],
            'status'      => ['sometimes', 'in:active,draft,inactive'],
        ]);

        $product->update($data);

        $product->load(['vendor', 'categories']);

        return new ProductResource($product);
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $product->delete();

        return response()->noContent();
    }
}
