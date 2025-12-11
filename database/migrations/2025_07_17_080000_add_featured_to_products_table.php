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
        // Add featured column to both products tables
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'featured')) {
                    $table->boolean('featured')->default(false)->after('status');
                }
            });
        }
        
        if (Schema::hasTable('products_optimized')) {
            Schema::table('products_optimized', function (Blueprint $table) {
                if (!Schema::hasColumn('products_optimized', 'featured')) {
                    $table->boolean('featured')->default(false)->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'featured')) {
                    $table->dropColumn('featured');
                }
            });
        }
        
        if (Schema::hasTable('products_optimized')) {
            Schema::table('products_optimized', function (Blueprint $table) {
                if (Schema::hasColumn('products_optimized', 'featured')) {
                    $table->dropColumn('featured');
                }
            });
        }
    }
};