<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images_optimized', function (Blueprint $table) {
            // Gallery-size WebP copy for the product page, so it doesn't load the original upload
            $table->string('large_url', 1024)->nullable()->after('thumb_url');
        });
    }

    public function down(): void
    {
        Schema::table('product_images_optimized', function (Blueprint $table) {
            $table->dropColumn('large_url');
        });
    }
};
