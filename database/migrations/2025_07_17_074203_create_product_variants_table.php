<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('sku', 100)->unique()->nullable();
            $table->string('name', 255)->nullable(); // e.g., "8GB DDR4 3600MHz RGB (Single Module)"
            $table->string('barcode', 128)->nullable();
            $table->decimal('price', 12, 2)->nullable(); // override price if variant priced differently
            $table->decimal('compare_price', 12, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->boolean('manage_stock')->default(true);
            $table->boolean('is_default')->default(false);
            $table->json('attributes')->nullable(); // variant attributes: {"capacity":"8GB","freq":"3600MHz"}
            $table->timestamps();
            
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            
            // Indexes
            $table->index(['product_id', 'is_default']);
            $table->index(['product_id', 'stock']);
            $table->index(['sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};