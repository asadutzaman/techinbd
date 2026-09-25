<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * carts/order_items were created with product_id FKs to the legacy `products` table,
 * but they hold products_optimized ids. Point them at the live catalog.
 * Order items keep their product_name/product_price snapshot, so deleting a product
 * nulls their product_id instead of deleting order history.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
        Schema::table('carts', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products_optimized')->cascadeOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->change();
            $table->foreign('product_id')->references('id')->on('products_optimized')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // The legacy products table is dropped by a later migration; nothing to point back to
    }
};
