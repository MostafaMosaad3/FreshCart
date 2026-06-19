<?php

namespace App\Repositories;

use Illuminate\Support\Collection;

/**
 * Shared contract for the analytics repository so the caching decorator
 * (CachedAnalyticsRepository) is substitutable for the concrete query
 * implementation (AnalyticsRepository) everywhere it's injected.
 */
interface AnalyticsRepositoryContract
{
    public function topProductsPerCategory(int $topN = 3): Collection;

    public function vendorMonthlySales(int $vendorId, int $monthsBack = 12): Collection;

    public function ratingTrend(string $type, int $id, int $monthsBack = 12): Collection;

    public function pricingQuartiles(int $vendorId): Collection;

    public function vendorHealthReport(int $vendorId): ?object;

    public function categoryPerformanceTree(): Collection;

    public function customerInsights(int $userId): ?object;
}
