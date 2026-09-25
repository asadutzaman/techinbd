<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images_optimized', function (Blueprint $table) {
            // Small WebP copy for product cards; the original `url` stays for the product page
            $table->string('thumb_url', 1024)->nullable()->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('product_images_optimized', function (Blueprint $table) {
            $table->dropColumn('thumb_url');
        });
    }
};
