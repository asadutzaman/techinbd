<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images_optimized', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('variant_id')->nullable();
            
            // Image data
            $table->string('url', 1024);
            $table->string('alt_text', 191)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_main')->default(false);
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('product_id')->references('id')->on('products_optimized')->onDelete('cascade');
            $table->foreign('variant_id')->references('id')->on('product_variants_optimized')->onDelete('cascade');
            
            // Optimized indexes
            $table->index(['product_id', 'is_main']); // For finding main product image
            $table->index(['variant_id', 'is_main']); // For finding main variant image
            $table->index(['product_id', 'sort_order']); // For ordered product images
            $table->index(['variant_id', 'sort_order']); // For ordered variant images
            
            // Note: Unique constraints for main images will be handled at application level
            // MySQL unique constraints don't work well with boolean fields and NULLs
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images_optimized');
    }
};