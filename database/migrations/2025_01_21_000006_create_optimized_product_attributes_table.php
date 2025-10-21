<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_attributes_optimized', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('attribute_id');
            $table->unsignedBigInteger('attribute_value_id')->nullable(); // For predefined values
            
            // Value storage
            $table->text('value'); // Store actual value for this product
            $table->decimal('numeric_value', 15, 4)->nullable(); // For numeric sorting/filtering
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('product_id')->references('id')->on('products_optimized')->onDelete('cascade');
            $table->foreign('attribute_id')->references('id')->on('attributes_optimized')->onDelete('cascade');
            $table->foreign('attribute_value_id')->references('id')->on('attribute_values_optimized')->onDelete('set null');
            
            // Optimized indexes
            $table->index(['product_id']); // For product attribute queries
            $table->index(['attribute_id']); // For attribute-based filtering
            $table->index(['attribute_value_id']); // For predefined value filtering
            $table->index(['numeric_value']); // For numeric filtering/sorting
            
            // Composite indexes for common filtering scenarios
            $table->index(['attribute_id', 'attribute_value_id'], 'prod_attr_attr_val_idx'); // Filter by attribute + value
            $table->index(['product_id', 'attribute_id'], 'prod_attr_prod_attr_idx'); // Product's specific attribute
            $table->index(['attribute_id', 'numeric_value'], 'prod_attr_attr_num_idx'); // Numeric attribute filtering
            
            // Ensure unique combination of product and attribute
            $table->unique(['product_id', 'attribute_id'], 'prod_attr_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attributes_optimized');
    }
};