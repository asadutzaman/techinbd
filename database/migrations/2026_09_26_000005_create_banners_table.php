<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Home page banner slider, managed in Admin → Banners
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->string('subtitle')->nullable();
            $table->string('button_text', 40)->nullable();
            $table->string('link_url', 500)->nullable();
            $table->boolean('show_text')->default(true);
            $table->string('image_path');
            $table->string('mobile_image_path')->nullable();
            $table->json('images')->nullable(); // generated WebP versions, see Banner::generateImages()
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
