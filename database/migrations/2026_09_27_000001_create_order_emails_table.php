<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every email sent to a customer about an order, and whether it went (App\Support\CustomerMail logs them).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // "placed" (the confirmation) or "status" (a status update)
            $table->string('kind', 30);
            // The order's status when the email went out
            $table->string('order_status', 20)->nullable();
            $table->string('recipient');
            $table->string('subject');
            $table->boolean('sent');
            $table->text('error')->nullable();
            $table->string('message_id')->nullable();
            // The admin who sent it; null when the shop sent it by itself (checkout)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_emails');
    }
};
