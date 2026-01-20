<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Adult Entry", "Child Entry"
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('type')->default('general'); // general, vip, group, etc.
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('AED');
            $table->integer('daily_capacity')->nullable(); // Max tickets per day
            $table->integer('min_quantity')->default(1);
            $table->integer('max_quantity')->default(10);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable(); // Age restrictions, etc.
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('is_active');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
