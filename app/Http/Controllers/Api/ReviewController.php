<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\CreateProductReviewRequest;
use App\Http\Requests\Review\CreateVendorReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Product;
use App\Models\Review;
use App\Models\Vendor;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function storeForProduct(CreateProductReviewRequest $request, Product $product)
    {
        $review = $product->reviews()->create([
            'user_id' => $request->user()->id,
            'rating' => $request->integer('rating'),
            'comment' => $request->input('comment'),
        ]);

        return new ReviewResource($review->load('user', 'reviewable'));
    }

    public function storeForVendor(CreateVendorReviewRequest $request, Vendor $vendor)
    {
        $review = $vendor->reviews()->create([
            'user_id' => $request->user()->id,
            'rating' => $request->integer('rating'),
            'comment' => $request->input('comment'),
        ]);

        return new ReviewResource($review->load('user', 'reviewable'));
    }

    public function indexForProduct(Product $product)
    {
        $reviews = $product->reviews()
            ->with(['user', 'reviewable'])
            ->latest()
            ->paginate(20);

        return ReviewResource::collection($reviews);
    }

    public function indexForVendor(Vendor $vendor)
    {
        $reviews = $vendor->reviews()
            ->with(['user', 'reviewable'])
            ->latest()
            ->paginate(20);

        return ReviewResource::collection($reviews);
    }

    public function mine(Request $request)
    {
        $reviews = $request->user()->reviews()
            ->with(['reviewable', 'user'])   // eager-load user too, or ReviewResource lazy-loads it per row
            ->latest()
            ->paginate(20);

        return ReviewResource::collection($reviews);
    }

    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);   // ReviewPolicy::delete — author or admin

        $review->delete();

        return response()->noContent();
    }
}
