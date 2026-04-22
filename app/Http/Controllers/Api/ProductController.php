<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::active()
        ->with('vendor')
            ->latest()
            ->paginate(15);

        return ProductResource::collection($products);
    }


    public function show(string $slug)
    {
        $product = Product::with(['vendor', 'categories'])
            ->where('slug', $slug)
            ->firstOrFail();

        return new ProductResource($product);
    }

}
