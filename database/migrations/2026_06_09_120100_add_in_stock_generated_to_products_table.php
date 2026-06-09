<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A generated `in_stock` column mirrors the `in_stock` attribute we send to
     * Meilisearch (`stock > 0`). Having it as a real column lets the same
     * `->where('in_stock', ...)` filter work on the Scout `collection` driver
     * (which filters on real DB columns) as well as on Meilisearch.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('in_stock')->virtualAs('stock > 0')->after('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('in_stock');
        });
    }
};
