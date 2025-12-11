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
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'is_menu')) {
                $table->boolean('is_menu')->default(false)->after('status');
            }
            if (!Schema::hasColumn('categories', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_menu');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'is_menu')) {
                $table->dropColumn('is_menu');
            }
            if (Schema::hasColumn('categories', 'is_featured')) {
                $table->dropColumn('is_featured');
            }
        });
    }
};