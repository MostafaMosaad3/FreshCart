<?php

namespace App\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Read-only analytical queries backed by MySQL window functions and CTEs.
 *
 * FreshCart is multi-vendor: an order has no vendor_id. A vendor's sales are
 * derived through order_items -> product_variants -> products.vendor_id, and a
 * product's category link lives in the `category_product` pivot.
 */
class AnalyticsRepository
{
    // Week 7 Day 2 — window functions

    public function topProductsPerCategory(int $topN = 3): Collection
    {
        $rows = DB::select("
            WITH ranked AS (
                SELECT p.id , p.name , p.rating_avg , p.reviews_count , cp.category_id ,
                    ROW_NUMBER() OVER (
                        PARTITION BY cp.category_id
                        ORDER BY p.rating_avg DESC , p.reviews_count DESC , p.id ASC
                    ) AS rn
                FROM products AS p
                JOIN category_product AS cp ON cp.product_id = p.id
                WHERE p.status = 'active'
            )
            SELECT id , name , rating_avg , reviews_count , category_id , rn
            FROM ranked
            WHERE rn <= ?
            ORDER BY category_id , rn
        ", [$topN]);

        return collect($rows);
    }

    public function vendorMonthlySales(int $vendorId, int $monthBack = 12): Collection
    {
        $rows = DB::select("
            WITH monthly AS (
                SELECT
                    DATE_FORMAT(o.created_at, '%Y-%m') AS month,
                    SUM(oi.line_total) AS monthly_sales
                FROM orders o
                JOIN order_items oi      ON oi.order_id = o.id
                JOIN product_variants pv ON pv.id = oi.variant_id
                JOIN products p          ON p.id = pv.product_id
                WHERE p.vendor_id = ?
                    AND o.status = 'delivered'
                    AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                GROUP BY month
            )
            SELECT
                month,
                monthly_sales,
                SUM(monthly_sales) OVER (
                    ORDER BY month
                    ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW
                ) AS cumulative_sales
            FROM monthly
            ORDER BY month
        ", [$vendorId, $monthBack]);

        return collect($rows);
    }

    public function ratingTrend(string $type, int $id, int $monthBack = 12): Collection
    {
        $rows = DB::select("
            WITH monthly AS (
                SELECT
                    DATE_FORMAT(created_at, '%Y-%m') AS month,
                    AVG(rating) AS rating_avg,
                    COUNT(*) AS reviews_in_month
                FROM reviews
                WHERE reviewable_type = ?
                    AND reviewable_id = ?
                    AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                GROUP BY month
            )
            SELECT
                month , rating_avg , reviews_in_month ,
                LAG(rating_avg) OVER (ORDER BY month) AS prev_rating ,
                rating_avg - LAG(rating_avg) OVER (ORDER BY month) AS delta
            FROM monthly
            ORDER BY month
        ", [$type, $id, $monthBack]);

        return collect($rows);
    }

    public function pricingQuartiles(int $vendorId): Collection
    {
        $rows = DB::select("
            SELECT
                id,
                name,
                price,
                NTILE(4) OVER (ORDER BY price ASC) AS price_quartile
            FROM products
            WHERE vendor_id = ?
              AND status = 'active'
            ORDER BY price ASC
        ", [$vendorId]);

        return collect($rows);
    }
}
