<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capacity_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            
            // Date specificity
            $table->date('date')->nullable();                // Specific date override
            $table->integer('day_of_week')->nullable();      // 0-6 (Sunday to Saturday)
            $table->date('date_from')->nullable();           // Date range
            $table->date('date_to')->nullable();
            
            // Time slot (if product is time-slot based)
            $table->time('time_slot_start')->nullable();
            $table->time('time_slot_end')->nullable();
            
            // Capacity
            $table->integer('capacity_override')->nullable(); // Overrides product default
            $table->string('reason')->nullable();             // e.g., "Special Event", "Maintenance"
            
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);          // Higher priority rules apply first
            
            $table->timestamps();
            
            $table->index(['product_id', 'date']);
            $table->index(['product_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capacity_rules');
    }
};
