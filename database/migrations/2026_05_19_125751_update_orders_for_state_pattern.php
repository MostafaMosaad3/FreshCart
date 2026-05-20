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
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('shipped_at')->nullable()->after('paid_at');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            $table->timestamp('cancelled_at')->nullable()->after('delivered_at');

            $table->string('tracking_number')->nullable()->after('cancelled_at');
            $table->string('cancellation_reason')->nullable()->after('tracking_number');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_index');
            $table->dropColumn('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'shipped', 'delivered', 'cancelled'])
                ->default('pending')
                ->after('total')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_index');
            $table->dropColumn('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('total');
            $table->index('status');
            $table->dropColumn([
                'shipped_at',
                'delivered_at',
                'cancelled_at',
                'tracking_number',
                'cancellation_reason',
            ]);
        });
    }
};
