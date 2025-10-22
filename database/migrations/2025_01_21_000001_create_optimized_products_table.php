<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products_optimized', function (Blueprint $table) {
            $table->id();
            
            // Core identifiers
            $table->string('sku', 100)->unique()->nullable();
            $table->char('uuid', 36)->unique()->nullable();
            
            // Basic info
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            
            // Descriptions
            $table->string('short_description', 512)->nullable();
            $table->text('description')->nullable(); // HTML description/specs
            
            // Pricing
            $table->decimal('base_price', 12, 2)->default(0);
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->string('currency', 10)->default('BDT');
            
            // Inventory management
            $table->boolean('manage_stock')->default(true);
            $table->enum('stock_status', ['in_stock', 'out_of_stock', 'preorder'])->default('in_stock');
            $table->integer('total_stock')->default(0); // aggregated stock (denormalized)
            
            // Physical attributes
            $table->decimal('weight', 10, 3)->nullable();
            $table->string('dimensions', 100)->nullable(); // e.g. "133x34x7 mm"
            
            // Flexible data storage
            $table->json('specs')->nullable(); // flexible key/value spec block (capacity, speed, CAS, voltage...)
            $table->json('attributes')->nullable(); // quick attribute set (if you don't use normalized table)
            
            // SEO
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description', 512)->nullable();
            $table->string('meta_keywords', 512)->nullable();
            
            // Status and additional info
            $table->tinyInteger('status')->default(1); // 1=active, 0=draft
            $table->boolean('featured')->default(false); // Featured product flag
            $table->string('warranty', 128)->nullable();
            $table->string('manufacturer_part_no', 128)->nullable();
            $table->string('ean_upc', 64)->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            
            // Optimized indexes for performance
            $table->index(['status', 'stock_status']); // For active product filtering
            $table->index(['category_id', 'status']); // For category pages
            $table->index(['brand_id', 'status']); // For brand filtering
            $table->index(['base_price']); // For price sorting/filtering
            $table->index(['created_at']); // For newest products
            $table->index(['slug']); // For SEO URLs
            $table->index(['sku']); // For SKU lookups
            $table->index(['total_stock']); // For stock filtering
            $table->index(['featured', 'status']); // For featured products
            
            // Composite indexes for common queries
            $table->index(['category_id', 'status', 'base_price'], 'prod_cat_status_price_idx'); // Category + price filtering
            $table->index(['brand_id', 'category_id', 'status'], 'prod_brand_cat_status_idx'); // Brand + category filtering
            $table->index(['status', 'stock_status', 'created_at'], 'prod_status_stock_date_idx'); // Active products by date
            $table->index(['featured', 'status', 'created_at'], 'prod_featured_status_date_idx'); // Featured products
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products_optimized');
    }
};