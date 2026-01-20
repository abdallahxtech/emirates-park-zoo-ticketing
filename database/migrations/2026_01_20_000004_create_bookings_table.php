<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_id')->unique();          // e.g., ZOO-2026-00123
            
            // Customer information
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            
            // Booking details
            $table->date('visit_date');
            $table->text('notes')->nullable();
            
            // Pricing
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('AED');
            
            // State management
            $table->enum('state', [
                'draft', 
                'pending_payment', 
                'payment_processing', 
                'confirmed', 
                'issued', 
                'used', 
                'expired', 
                'cancelled'
            ])->default('draft');
            $table->timestamp('hold_expires_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            
            // Source tracking
            $table->enum('source', ['online', 'offline', 'admin'])->default('online');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Timestamps
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index('state');
            $table->index('visit_date');
            $table->index('customer_email');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
