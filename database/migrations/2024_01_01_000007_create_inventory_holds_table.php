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
        Schema::create('inventory_holds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->date('visit_date');
            $table->integer('quantity');
            
            // Hold expiry
            $table->timestamp('expires_at');
            $table->boolean('is_released')->default(false);
            $table->timestamp('released_at')->nullable();
            $table->string('release_reason')->nullable(); // expired, payment_success, cancelled
            
            $table->timestamps();

            // Indexes for inventory queries
            $table->index(['ticket_id', 'visit_date', 'is_released']);
            $table->index('expires_at');
            $table->index('booking_id');
            $table->index(['is_released', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_holds');
    }
};
