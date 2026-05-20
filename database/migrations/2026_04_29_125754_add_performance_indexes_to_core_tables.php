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
            $table->index(['status', 'created_at'], 'products_status_created_at_idx');

            $table->index(['vendor_id', 'status'], 'products_vendor_status_idx');

            $table->index(['is_featured', 'status'], 'products_is_featured_status_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'users_role_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $t) {
            $t->dropIndex('products_status_created_at_idx');
            $t->dropIndex('products_vendor_status_idx');
            $t->dropIndex('products_featured_status_idx');
        });

        Schema::table('users', function (Blueprint $t) {
            $t->dropIndex('users_role_idx');
        });
    }
};
