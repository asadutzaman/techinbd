<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The storefront, admin, carts and orders all use products_optimized; the original
 * `products` / `product_variants` tables have no code left that reads them.
 * Back up the database before running this against data you care about.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
    }

    public function down(): void
    {
        // Recreates the tables empty; the dropped rows are only in your backup
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 100)->unique()->nullable();
            $table->char('uuid', 36)->unique()->nullable();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('short_description', 512)->nullable();
            $table->text('description')->nullable();
            $table->decimal('base_price', 12, 2)->default(0);
            $table->string('currency', 10)->default('BDT');
            $table->enum('stock_status', ['in_stock', 'out_of_stock', 'preorder'])->default('in_stock');
            $table->integer('total_stock')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->boolean('featured')->default(false);
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku', 100)->unique()->nullable();
            $table->string('name', 255)->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }
};
