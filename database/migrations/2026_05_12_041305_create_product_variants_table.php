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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete() ;
            $table->string('sku');
            $table->string('name');
            $table->decimal('price' , 10 , 2);
            $table->json('attributes')->nullable();
            $table->unsignedBigInteger('stock')->default(0) ;
            $table->unsignedBigInteger('version')->default(0) ;

            $table->timestamps();

            $table->unique(['product_id', 'sku']);
            $table->index(['product_id', 'sku']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
