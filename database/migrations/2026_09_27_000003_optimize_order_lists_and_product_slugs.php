<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Keeps the admin order screens fast as orders pile up, and lets two products share a name.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Whether the latest email to the customer failed. OrderEmail keeps it up to date, so the admin can
            // count and list those orders without reading every order's email log.
            $table->boolean('last_email_failed')->default(false)->index();
            // The status tabs and the pending count (the sidebar badge on every admin page), and newest-first lists
            $table->index(['status', 'created_at']);
            $table->index('created_at');
        });

        DB::table('orders')
            ->where(
                DB::table('order_emails')->select('sent')->whereColumn('order_emails.order_id', 'orders.id')->orderByDesc('order_emails.id')->limit(1),
                false
            )
            ->update(['last_email_failed' => true]);

        // The slug is made from the name and no URL uses it (product links use the id), so keeping it
        // unique only stopped two products from having the same name. The plain slug index stays.
        Schema::table('products_optimized', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });
    }

    public function down(): void
    {
        // Fails if two products share a slug by then
        Schema::table('products_optimized', function (Blueprint $table) {
            $table->unique('slug');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['last_email_failed']);
            $table->dropColumn('last_email_failed');
        });
    }
};
