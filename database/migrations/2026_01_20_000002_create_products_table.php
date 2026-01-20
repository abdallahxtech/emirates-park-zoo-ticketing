<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('product_categories')->cascadeOnDelete();
            
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();   // For API listing
            $table->string('featured_image')->nullable();
            
            // Pricing
            $table->decimal('base_price', 10, 2);
            $table->string('currency', 3)->default('AED');
            
            // Capacity
            $table->integer('daily_capacity')->nullable();   // NULL = unlimited
            $table->boolean('is_time_slot_based')->default(false);
            
            // Ticket validity
            $table->integer('validity_days')->default(1);    // Days from visit date
            
            // Restrictions
            $table->integer('min_quantity')->default(1);
            $table->integer('max_quantity')->nullable();     // Per booking
            $table->boolean('requires_age_verification')->default(false);
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
