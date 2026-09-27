<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Product and category pages are addressed by slug (/product/{slug}, /category/{slug}), so product slugs are
 * unique again. Addresses that changed keep working through slug_redirects (see App\Models\Concerns\HasSlug).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slug_redirects', function (Blueprint $table) {
            $table->id();
            // "product" or "category"
            $table->string('type', 20);
            // A slug the page used to have
            $table->string('slug');
            $table->unsignedBigInteger('target_id');
            $table->timestamps();

            $table->unique(['type', 'slug']);
            $table->index(['type', 'target_id']);
        });

        // Same rules as HasSlug: URL-safe, never all digits (that reads as an old /product/{id} address), and a
        // number added to repeats, oldest product first. Category slugs are already unique and URL-safe.
        $taken = [];
        foreach (DB::table('products_optimized')->orderBy('id')->get(['id', 'name', 'slug']) as $product) {
            $base = trim(Str::limit(Str::slug($product->slug ?: $product->name), 190, ''), '-') ?: 'product';
            if (ctype_digit($base)) {
                $base = "product-{$base}";
            }
            $slug = $base;
            for ($n = 2; isset($taken[$slug]); $n++) {
                $slug = "{$base}-{$n}";
            }
            $taken[$slug] = true;

            if ($slug !== $product->slug) {
                DB::table('products_optimized')->where('id', $product->id)->update(['slug' => $slug]);
            }
        }

        Schema::table('products_optimized', function (Blueprint $table) {
            $table->unique('slug');
            // The unique index serves lookups too
            $table->dropIndex(['slug']);
        });
    }

    public function down(): void
    {
        Schema::table('products_optimized', function (Blueprint $table) {
            $table->index('slug');
            $table->dropUnique(['slug']);
        });

        Schema::dropIfExists('slug_redirects');
    }
};
