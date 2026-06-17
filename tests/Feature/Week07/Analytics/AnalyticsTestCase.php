<?php

namespace Tests\Feature\Week07\Analytics;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Base test case for the analytics suite.
 *
 * The analytics queries use MySQL-only features (window functions, recursive
 * CTEs, DATE_FORMAT, GROUP_CONCAT, ...), which the default sqlite :memory: test
 * connection cannot run. These tests therefore run against the dedicated
 * `mysql_testing` connection (see config/database.php).
 *
 * The schema is migrated fresh once per test class and every test runs inside a
 * transaction that is rolled back on teardown, so tests stay isolated without
 * paying for a full migrate between each one.
 */
abstract class AnalyticsTestCase extends TestCase
{
    protected static bool $schemaMigrated = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Review creation fires an observer that notifies vendor/product owners;
        // neutralise it so analytics data setup has no outward side effects.
        Notification::fake();
        Mail::fake();

        config(['database.default' => 'mysql_testing']);
        DB::setDefaultConnection('mysql_testing');

        if (! static::$schemaMigrated) {
            Artisan::call('migrate:fresh', ['--database' => 'mysql_testing', '--force' => true]);
            static::$schemaMigrated = true;
        }

        DB::connection('mysql_testing')->beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::connection('mysql_testing')->rollBack();

        parent::tearDown();
    }
}
