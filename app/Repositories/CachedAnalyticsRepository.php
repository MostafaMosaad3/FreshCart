<?php

namespace App\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CachedAnalyticsRepository implements AnalyticsRepositoryContract
{
    public function __construct(private AnalyticsRepository $analyticsRepository) {}

    public function topProductsPerCategory(int $top = 3): Collection
    {
        $key = "analytics.top_products.v1.top_n={$top}";

        return Cache::remember($key, 900, fn () => $this->analyticsRepository->topProductsPerCategory($top));
    }

    public function vendorHealthReport(int $vendorId): ?object
    {
        $key = "analytics.vendor_health.v1.vendor={$vendorId}";

        return Cache::lock("{$key}.lock", 10)->block(5, function () use ($key, $vendorId) {
            return Cache::remember($key, 300, fn () => $this->analyticsRepository->vendorHealthReport($vendorId));
        });
    }

    public function CategoryPerformanceTree(): Collection
    {
        $key = 'analytics.category.performance_tree.v1';

        return Cache::lock("{$key}.lock", 10)->block(5, function () use ($key) {
            return Cache::remember($key, 600, fn () => $this->analyticsRepository->categoryPerformanceTree());
        });
    }

    public function customerInsights(int $userId): ?object
    {
        $key = "analytics.customer_insights.v1.{$userId}";

        return Cache::remember($key, 600, fn () => $this->analyticsRepository->customerInsights($userId));
    }

    public function vendorMonthlySales(int $vendorId, int $monthsBack = 12): Collection
    {
        $key = "analytics.vendor_monthly_sales.v1.vendor={$vendorId}.months={$monthsBack}";

        return Cache::remember($key, 600, fn () => $this->analyticsRepository->vendorMonthlySales($vendorId, $monthsBack));
    }

    public function ratingTrend(string $type, int $id, int $monthsBack = 12): Collection
    {
        $key = "analytics.rating_trend.v1.{$type}.{$id}.months={$monthsBack}";

        return Cache::remember($key, 600, fn () => $this->analyticsRepository->ratingTrend($type, $id, $monthsBack));
    }

    public function pricingQuartiles(int $vendorId): Collection
    {
        $key = "analytics.pricing_quartiles.v1.{$vendorId}";

        return Cache::remember($key, 1800, fn () => $this->analyticsRepository->pricingQuartiles($vendorId));
    }
}
