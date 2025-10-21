<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_values_optimized', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attribute_id');
            
            // Value data
            $table->string('value', 255); // like "1920x1080", "512GB", "16GB", "i7", "48MP"
            $table->string('display_value', 255)->nullable(); // Optional display name
            $table->string('slug', 255)->nullable(); // For URL-friendly filtering
            
            // Additional metadata
            $table->string('color_code', 7)->nullable(); // For color attributes (#FF0000)
            $table->decimal('numeric_value', 15, 4)->nullable(); // For sorting numeric values
            $table->string('unit', 20)->nullable(); // Override attribute unit if needed
            
            // Display options
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(true);
            
            $table->timestamps();
            
            // Foreign key
            $table->foreign('attribute_id')->references('id')->on('attributes_optimized')->onDelete('cascade');
            
            // Optimized indexes
            $table->index(['attribute_id', 'status']); // For active values
            $table->index(['attribute_id', 'sort_order']); // For ordered values
            $table->index(['slug']); // For URL-based filtering
            $table->index(['numeric_value']); // For numeric sorting
            $table->index(['value']); // For value lookups
            
            // Composite indexes
            $table->index(['attribute_id', 'status', 'sort_order'], 'attr_val_status_sort_idx'); // Common value queries
            
            // Unique constraint for value per attribute
            $table->unique(['attribute_id', 'value'], 'attr_val_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_values_optimized');
    }
};