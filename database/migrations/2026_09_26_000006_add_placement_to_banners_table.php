<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Where a banner shows on the home page: the main slider, or the column beside it (see Banner::PLACEMENTS)
        Schema::table('banners', function (Blueprint $table) {
            $table->string('placement', 10)->default('slider')->after('link_url');
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('placement');
        });
    }
};
