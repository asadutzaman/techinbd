<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attributes_optimized', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable(); // Allow global attributes
            
            // Basic info
            $table->string('name', 100); // e.g., "Resolution", "Storage", "RAM"
            $table->string('slug', 100)->unique(); // For URL-friendly filtering
            $table->enum('type', ['text', 'number', 'select', 'boolean', 'textarea', 'color', 'date'])->default('text');
            
            // Behavior flags
            $table->boolean('required')->default(false);
            $table->boolean('filterable')->default(true); // Can be used as filter in shop
            $table->boolean('searchable')->default(false); // Include in search index
            $table->boolean('comparable')->default(false); // Show in product comparison
            
            // Display options
            $table->string('unit', 20)->nullable(); // e.g., "GB", "MHz", "inches"
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(true);
            
            $table->timestamps();
            
            // Foreign key (nullable for global attributes)
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            
            // Optimized indexes
            $table->index(['category_id', 'status']); // For category-specific attributes
            $table->index(['filterable', 'status']); // For filter generation
            $table->index(['slug']); // For URL-based filtering
            $table->index(['sort_order']); // For ordered display
            $table->index(['type']); // For type-specific queries
            
            // Composite indexes
            $table->index(['category_id', 'filterable', 'status'], 'attr_cat_filt_status_idx'); // Common filter queries
            $table->index(['status', 'sort_order'], 'attr_status_sort_idx'); // Active attributes in order
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attributes_optimized');
    }
};