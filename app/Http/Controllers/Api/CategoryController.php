<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryNestedResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function tree()
    {
        $flat = Cache::remember('categories.tree.flat.v1', 3360, function () {
            $roots = Category::loadTree() ;
            return self::flatten($roots)->values();
        });
    }

    public function nested()
    {
        $roots = Category::loadTree();
        return CategoryNestedResource::collection($roots);
    }

    public function products(Category $category)
    {
        $categoryIds = $category->getDescendantIds();

        $products = Product::query()
            ->where('status', 'active')
            ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds))
            ->with(['vendor', 'categories'])
            ->get();

        return ProductResource::collection($products);
    }


    protected static function flatten($nodes, $acc = null)
    {
        $acc = $acc ?? collect();
        foreach ($nodes as $node) {
            $acc->push($node);
            self::flatten($node->children, $acc);
        }
        return $acc;
    }
}
