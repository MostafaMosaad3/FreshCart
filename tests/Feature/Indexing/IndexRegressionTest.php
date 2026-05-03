<?php

namespace Tests\Feature\Indexing;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IndexRegressionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // These tests query information_schema and EXPLAIN — MySQL only.
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Index regression tests require MySQL.');
        }
    }

    public function test_has_the_expected_performance_indexes_on_products(): void
    {
        $indexes = collect(DB::select("
            SELECT INDEX_NAME
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = 'products'
        "))->pluck('INDEX_NAME')->unique()->values()->all();

        $this->assertContains('products_status_created_at_idx', $indexes);
        $this->assertContains('products_vendor_status_idx', $indexes);
        $this->assertContains('products_featured_status_idx', $indexes);
    }

    public function test_uses_an_index_not_a_table_scan_for_the_public_catalog_query(): void
    {
        $plan = DB::select("
            EXPLAIN SELECT id, name, price
            FROM products
            WHERE status = 'active'
            ORDER BY created_at DESC
            LIMIT 20
        ")[0];

        $this->assertNotSame('ALL', $plan->type);
    }

    public function test_uses_an_index_for_vendor_admin_listing(): void
    {
        $plan = DB::select("
            EXPLAIN SELECT id, name, status
            FROM products
            WHERE vendor_id = 1 AND status = 'active'
        ")[0];

        $this->assertContains($plan->type, ['ref', 'range', 'const', 'eq_ref']);
    }
}
