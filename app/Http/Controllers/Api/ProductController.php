<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('vendor');
        $user = $request->user('sanctum');

        if ($user?->isAdmin()) {
            // admin sees everything
        } elseif ($user?->isVendor() && $user->vendor) {
            $query->where('vendor_id', $user->vendor->id);
        } else {
            $query->where('status', 'active')
                ->whereHas('vendor', fn ($q) => $q->where('is_verified', true));
        }

        return ProductResource::collection($query->latest()->paginate(15));
    }

    public function show(Product $product)
    {
        $this->authorize('view', $product);

        $product->load(['vendor', 'categories']);

        return new ProductResource($product);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $categoryIds = $data['category_ids'] ?? [];
        unset($data['category_ids']);

        $product = $request->user()->vendor->products()->create($data);

        if (! empty($categoryIds)) {
            $product->categories()->sync($categoryIds);
        }

        $product->load(['vendor', 'categories']);

        return (new ProductResource($product))->response()->setStatusCode(201);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();
        $categoryIds = $data['category_ids'] ?? null;
        unset($data['category_ids']);

        $product->update($data);

        if ($categoryIds !== null) {
            $product->categories()->sync($categoryIds);
        }

        return new ProductResource($product->fresh(['vendor', 'categories']));
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $product->delete();

        return response()->noContent();
    }
}
