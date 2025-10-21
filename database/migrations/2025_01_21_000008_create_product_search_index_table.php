<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Denormalized search index table for better search performance
        Schema::create('product_search_index', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            
            // Searchable content (denormalized for performance)
            $table->text('searchable_content'); // Combined: name, description, brand, category, attributes
            $table->string('sku', 100)->nullable();
            $table->string('name', 255);
            $table->string('brand_name', 100)->nullable();
            $table->string('category_names', 500)->nullable(); // Comma-separated category hierarchy
            $table->text('attribute_values')->nullable(); // Searchable attribute values
            
            // For filtering and sorting
            $table->decimal('price', 12, 2)->default(0);
            $table->tinyInteger('status')->default(1);
            $table->enum('stock_status', ['in_stock', 'out_of_stock', 'preorder'])->default('in_stock');
            $table->integer('total_stock')->default(0);
            
            $table->timestamps();
            
            // Foreign key
            $table->foreign('product_id')->references('id')->on('products_optimized')->onDelete('cascade');
            
            // Full-text search index
            $table->fullText(['searchable_content', 'name', 'brand_name', 'attribute_values'], 'prod_search_fulltext');
            
            // Regular indexes for filtering
            $table->index(['status', 'stock_status']); // Active products
            $table->index(['price']); // Price sorting
            $table->index(['total_stock']); // Stock filtering
            $table->index(['sku']); // SKU search
            
            // Unique constraint
            $table->unique(['product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_search_index');
    }
};