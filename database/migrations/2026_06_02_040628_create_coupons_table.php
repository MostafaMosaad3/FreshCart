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
        // POLICY: single coupon per order. The cart carries one coupon_id, copied
        // onto the order at checkout. Applying a second coupon replaces the first.
        // Revisit (coupon_order pivot) only if product approves stacking. See docs/coupons.md.
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('strategy')->index();
            $table->json('config');
            $table->decimal('min_subtotal', 10, 2)->nullable();
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('max_per_user')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('first_order_only')->default(false);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->string('description')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
