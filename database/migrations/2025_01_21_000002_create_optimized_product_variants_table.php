<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants_optimized', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            
            // Identifiers
            $table->string('sku', 100)->unique()->nullable();
            $table->string('name', 255)->nullable(); // e.g., "8GB DDR4 3600MHz RGB (Single Module)"
            $table->string('barcode', 128)->nullable();
            
            // Pricing
            $table->decimal('price', 12, 2)->nullable(); // override price if variant priced differently
            $table->decimal('compare_price', 12, 2)->nullable();
            
            // Inventory
            $table->integer('stock')->default(0);
            $table->boolean('manage_stock')->default(true);
            $table->boolean('is_default')->default(false);
            
            // Variant-specific attributes (JSON for flexibility)
            $table->json('attributes')->nullable(); // variant attributes: {"capacity":"8GB","freq":"3600MHz"}
            
            $table->timestamps();
            
            // Foreign key
            $table->foreign('product_id')->references('id')->on('products_optimized')->onDelete('cascade');
            
            // Optimized indexes
            $table->index(['product_id', 'is_default']); // For finding default variant
            $table->index(['product_id', 'stock']); // For stock filtering
            $table->index(['sku']); // For SKU lookups
            $table->index(['stock']); // For inventory management
            $table->index(['price']); // For price sorting
            
            // Composite indexes
            $table->index(['product_id', 'stock', 'is_default'], 'prod_var_stock_def_idx'); // Common variant queries
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants_optimized');
    }
};