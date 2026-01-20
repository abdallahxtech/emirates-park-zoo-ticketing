<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            
            $table->string('name');                          // e.g., "Weekend Premium"
            $table->enum('rule_type', ['weekday', 'weekend', 'seasonal', 'group_discount', 'early_bird']);
            
            // Condition
            $table->json('conditions')->nullable();          // e.g., {"min_quantity": 5} or {"day_of_week": [6,0]}
            
            // Adjustment
            $table->enum('adjustment_type', ['fixed', 'percentage']);
            $table->decimal('adjustment_value', 10, 2);
            
            // Date range
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);         // Higher priority rules apply first
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
