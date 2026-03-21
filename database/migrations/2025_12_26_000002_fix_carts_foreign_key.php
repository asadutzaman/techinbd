<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['product_id']);
            
            // Add the new foreign key constraint pointing to products_optimized
            $table->foreign('product_id')->references('id')->on('products_optimized')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            // Drop the new foreign key constraint
            $table->dropForeign(['product_id']);
            
            // Restore the original foreign key constraint
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }
};