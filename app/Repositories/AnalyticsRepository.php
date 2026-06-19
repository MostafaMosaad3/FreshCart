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
class AnalyticsRepository implements AnalyticsRepositoryContract
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

    // Week 7 Day 3 — advanced / chained CTEs

    public function vendorHealthReport(int $vendorId): ?object
    {
        $rows = DB::select("
        WITH
        recent_sales AS (
            SELECT p.vendor_id, COALESCE(SUM(oi.line_total), 0) AS total
            FROM orders o
            JOIN order_items oi      ON oi.order_id = o.id
            JOIN product_variants pv ON pv.id = oi.variant_id
            JOIN products p          ON p.id = pv.product_id
            WHERE p.vendor_id = ?
              AND o.status = 'delivered'
              AND o.created_at >= NOW() - INTERVAL 30 DAY
            GROUP BY p.vendor_id
        ),
        prior_sales AS (
            SELECT p.vendor_id, COALESCE(SUM(oi.line_total), 0) AS total
            FROM orders o
            JOIN order_items oi      ON oi.order_id = o.id
            JOIN product_variants pv ON pv.id = oi.variant_id
            JOIN products p          ON p.id = pv.product_id
            WHERE p.vendor_id = ?
              AND o.status = 'delivered'
              AND o.created_at >= NOW() - INTERVAL 60 DAY
              AND o.created_at <  NOW() - INTERVAL 30 DAY
            GROUP BY p.vendor_id
        ),
        cancellation_rate AS (
            SELECT p.vendor_id,
                   CASE WHEN COUNT(DISTINCT o.id) = 0 THEN 0
                        ELSE COUNT(DISTINCT CASE WHEN o.status = 'cancelled' THEN o.id END) * 1.0
                             / COUNT(DISTINCT o.id)
                   END AS rate
            FROM orders o
            JOIN order_items oi      ON oi.order_id = o.id
            JOIN product_variants pv ON pv.id = oi.variant_id
            JOIN products p          ON p.id = pv.product_id
            WHERE p.vendor_id = ?
            GROUP BY p.vendor_id
        ),
        recent_rating AS (
            SELECT reviewable_id AS vendor_id,
                   AVG(rating) AS avg_rating,
                   COUNT(*) AS review_count
            FROM reviews
            WHERE reviewable_type = 'vendor'
              AND reviewable_id = ?
              AND created_at >= NOW() - INTERVAL 30 DAY
            GROUP BY reviewable_id
        ),
        top_products AS (
            SELECT vendor_id,
                   GROUP_CONCAT(name ORDER BY rating_avg DESC SEPARATOR ', ') AS product_names
            FROM (
                SELECT vendor_id, name, rating_avg,
                       ROW_NUMBER() OVER (PARTITION BY vendor_id ORDER BY rating_avg DESC, id ASC) AS rn
                FROM products
                WHERE vendor_id = ? AND status = 'active'
            ) ranked
            WHERE rn <= 3
            GROUP BY vendor_id
        )
        SELECT
            v.id,
            v.store_name,
            COALESCE(rs.total, 0) AS recent_sales,
            COALESCE(ps.total, 0) AS prior_sales,
            CASE
                WHEN ps.total IS NULL OR ps.total = 0 THEN NULL
                ELSE ROUND((rs.total - ps.total) / ps.total * 100, 2)
            END AS sales_growth_pct,
            COALESCE(cr.rate, 0) AS cancel_rate,
            rr.avg_rating AS recent_rating,
            COALESCE(rr.review_count, 0) AS recent_reviews,
            tp.product_names AS top_products
        FROM vendors v
            LEFT JOIN recent_sales rs        ON rs.vendor_id = v.id
            LEFT JOIN prior_sales ps         ON ps.vendor_id = v.id
            LEFT JOIN cancellation_rate cr   ON cr.vendor_id = v.id
            LEFT JOIN recent_rating rr       ON rr.vendor_id = v.id
            LEFT JOIN top_products tp        ON tp.vendor_id = v.id
        WHERE v.id = ?
    ", [$vendorId, $vendorId, $vendorId, $vendorId, $vendorId, $vendorId]);

        return $rows[0] ?? null;
    }

    public function categoryPerformanceTree(): Collection
    {
        $rows = DB::select("
        WITH RECURSIVE category_tree AS (
            SELECT id,
                   name,
                   parent_id,
                   id AS root_id,
                   0 AS depth,
                   CAST(id AS CHAR(1000)) AS path
            FROM categories
            WHERE parent_id IS NULL

            UNION ALL

            SELECT c.id,
                   c.name,
                   c.parent_id,
                   ct.root_id,
                   ct.depth + 1,
                   CONCAT(ct.path, ',', c.id)
            FROM categories c
            JOIN category_tree ct ON c.parent_id = ct.id
            WHERE ct.depth < 50
              AND FIND_IN_SET(c.id, ct.path) = 0
        ),
        tree_products AS (
            SELECT ct.root_id,
                   ct.id AS category_id,
                   COUNT(DISTINCT p.id) AS product_count
            FROM category_tree ct
            LEFT JOIN category_product cp ON cp.category_id = ct.id
            LEFT JOIN products p ON p.id = cp.product_id AND p.status = 'active'
            GROUP BY ct.root_id, ct.id
        ),
        tree_revenue AS (
            SELECT ct.root_id,
                   COALESCE(SUM(CASE WHEN o.id IS NOT NULL THEN oi.line_total ELSE 0 END), 0) AS revenue
            FROM category_tree ct
            LEFT JOIN category_product cp   ON cp.category_id = ct.id
            LEFT JOIN product_variants pv   ON pv.product_id = cp.product_id
            LEFT JOIN order_items oi         ON oi.variant_id = pv.id
            LEFT JOIN orders o               ON o.id = oi.order_id AND o.status = 'delivered'
            GROUP BY ct.root_id
        )
        SELECT
            roots.id,
            roots.name,
            COALESCE(SUM(tp.product_count), 0) AS total_products,
            MAX(tr.revenue) AS total_revenue
        FROM categories roots
        LEFT JOIN tree_products tp ON tp.root_id = roots.id
        LEFT JOIN tree_revenue tr  ON tr.root_id = roots.id
        WHERE roots.parent_id IS NULL
        GROUP BY roots.id, roots.name
        ORDER BY total_revenue DESC, roots.name
    ");

        return collect($rows);
    }

    public function customerInsights(int $userId): ?object
    {
        $rows = DB::select("
        WITH
        delivered_orders AS (
            SELECT id, user_id, total, created_at
            FROM orders
            WHERE user_id = ? AND status = 'delivered'
        ),
        order_summary AS (
            SELECT
                user_id,
                COUNT(*) AS total_orders,
                SUM(total) AS lifetime_value,
                AVG(total) AS avg_order_value,
                MIN(created_at) AS first_order,
                MAX(created_at) AS last_order
            FROM delivered_orders
            GROUP BY user_id
        ),
        category_spend AS (
            SELECT
                do.user_id,
                cp.category_id,
                SUM(oi.line_total) AS spend
            FROM delivered_orders do
            JOIN order_items oi      ON oi.order_id = do.id
            JOIN product_variants pv ON pv.id = oi.variant_id
            JOIN category_product cp ON cp.product_id = pv.product_id
            GROUP BY do.user_id, cp.category_id
        ),
        ranked_categories AS (
            SELECT
                cs.*,
                c.name AS category_name,
                ROW_NUMBER() OVER (PARTITION BY cs.user_id ORDER BY cs.spend DESC) AS rn
            FROM category_spend cs
            JOIN categories c ON c.id = cs.category_id
        ),
        top_categories AS (
            SELECT user_id,
                   GROUP_CONCAT(category_name ORDER BY rn SEPARATOR ', ') AS favorites
            FROM ranked_categories
            WHERE rn <= 3
            GROUP BY user_id
        )
        SELECT
            u.id,
            u.name,
            u.email,
            COALESCE(os.total_orders, 0) AS total_orders,
            COALESCE(os.lifetime_value, 0) AS lifetime_value,
            os.avg_order_value,
            os.first_order,
            os.last_order,
            DATEDIFF(NOW(), os.first_order) AS days_as_customer,
            tc.favorites AS favorite_categories
        FROM users u
            LEFT JOIN order_summary os ON os.user_id = u.id
            LEFT JOIN top_categories tc ON tc.user_id = u.id
        WHERE u.id = ?
    ", [$userId, $userId]);

        $row = $rows[0] ?? null;

        // The outer LEFT JOIN keeps the user row even when they have no delivered
        // orders. Treat "no activity" the same as "not found" so the controller can
        // turn it into a 404.
        return ($row && (int) $row->total_orders > 0) ? $row : null;
    }
}
