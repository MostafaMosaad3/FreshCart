<?php

namespace App\Http\Controllers;

use App\Repositories\AnalyticsRepository;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(private AnalyticsRepository $repo) {}

    public function topProductsPerCategory(Request $request)
    {
        $topN = (int) $request->query('top_n', 3);
        abort_unless($topN >= 1 && $topN <= 10, 422, 'top_n must be 1..10');

        return $this->repo->topProductsPerCategory($topN);
    }

    public function vendorMonthlySales(int $vendorId, Request $request)
    {
        $months = (int) $request->query('months', 12);
        abort_unless($months >= 1 && $months <= 60, 422);

        return $this->repo->vendorMonthlySales($vendorId, $months);
    }

    public function ratingTrend(string $type, int $id, Request $request)
    {
        abort_unless(in_array($type, ['product', 'vendor'], true), 422);

        $months = (int) $request->query('months', 12);

        return $this->repo->ratingTrend($type, $id, $months);
    }

    public function pricingQuartiles(int $vendorId)
    {
        return $this->repo->pricingQuartiles($vendorId);
    }

    public function vendorHealthReport(int $vendorId): ?object
    {
        return $this->repo->vendorHealthReport($vendorId)
            ?? abort(404, 'Vendor not found or has no activity');
    }

    public function categoryPerformanceTree()
    {
        return $this->repo->categoryPerformanceTree();
    }

    public function customerInsights(int $userId)
    {
        return $this->repo->customerInsights($userId)
            ?? abort(404, 'User not found or has no orders');
    }
}
