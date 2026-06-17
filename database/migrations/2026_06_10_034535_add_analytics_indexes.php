<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(['status', 'rating_avg', 'reviews_count'], 'idx_products_analytics');
        });

        // Orders carry no vendor_id (multi-vendor: a vendor's sales are derived through
        // order_items -> product_variants -> products). Index the status + date filter used
        // by the analytics queries. The order_items.variant_id join is already covered by
        // its foreign-key index.
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'idx_orders_status_created');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['reviewable_type', 'reviewable_id', 'created_at'], 'idx_reviews_trend');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', fn (Blueprint $table) => $table->dropIndex('idx_products_analytics'));
        Schema::table('orders', fn (Blueprint $table) => $table->dropIndex('idx_orders_status_created'));
        Schema::table('reviews', fn (Blueprint $table) => $table->dropIndex('idx_reviews_trend'));
    }
};
