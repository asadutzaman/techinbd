<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 100)->unique()->nullable();
            $table->char('uuid', 36)->unique()->nullable();
            $table->string('name', 255);
            $table->string('slug', 255)->unique()->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('short_description', 512)->nullable();
            $table->text('description')->nullable(); // HTML description/specs
            $table->decimal('base_price', 12, 2)->default(0);
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->string('currency', 10)->default('BDT');
            $table->boolean('manage_stock')->default(true);
            $table->enum('stock_status', ['in_stock', 'out_of_stock', 'preorder'])->default('in_stock');
            $table->integer('total_stock')->default(0); // aggregated stock
            $table->decimal('weight', 10, 3)->nullable();
            $table->string('dimensions', 100)->nullable(); // e.g. "133x34x7 mm"
            $table->json('specs')->nullable(); // flexible key/value spec block
            $table->json('attributes')->nullable(); // quick attribute set
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description', 512)->nullable();
            $table->string('meta_keywords', 512)->nullable();
            $table->tinyInteger('status')->default(1); // 1=active, 0=draft
            $table->string('warranty', 128)->nullable();
            $table->string('manufacturer_part_no', 128)->nullable();
            $table->string('ean_upc', 64)->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            
            // Indexes for performance
            $table->index(['status', 'stock_status']);
            $table->index(['category_id', 'status']);
            $table->index(['brand_id', 'status']);
            $table->index(['base_price']);
            $table->index(['created_at']);
            $table->index(['slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
