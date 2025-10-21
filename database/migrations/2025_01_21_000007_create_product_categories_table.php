<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Many-to-many relationship for products that can belong to multiple categories
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('category_id');
            $table->boolean('is_primary')->default(false); // Mark primary category
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('product_id')->references('id')->on('products_optimized')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            
            // Indexes
            $table->index(['product_id']); // For product's categories
            $table->index(['category_id']); // For category's products
            $table->index(['is_primary']); // For primary category queries
            
            // Composite indexes
            $table->index(['category_id', 'is_primary']); // Category with primary products
            $table->index(['product_id', 'is_primary']); // Product's primary category
            
            // Unique constraint
            $table->unique(['product_id', 'category_id'], 'prod_cat_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};