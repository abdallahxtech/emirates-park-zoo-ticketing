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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique(); // e.g., BOOK-2024-001234
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            
            // State tracking
            $table->string('state')->default('DRAFT');
            $table->timestamp('state_changed_at')->nullable();
            
            // Pricing
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('currency', 3)->default('AED');
            
            // Hold management
            $table->timestamp('hold_expires_at')->nullable();
            $table->boolean('is_hold_expired')->virtualAs(
                'CASE WHEN hold_expires_at IS NOT NULL AND hold_expires_at < NOW() THEN true ELSE false END'
            );
            
            // Visit details
            $table->date('visit_date')->nullable();
            $table->time('visit_time')->nullable();
            
            // Payment metadata
            $table->string('payment_method')->nullable(); // card, cash, bank_transfer
            $table->string('payment_provider')->nullable(); // cybersource
            $table->string('payment_reference')->nullable();
            
            // Galaxy integration
            $table->string('galaxy_booking_id')->nullable();
            $table->json('galaxy_tickets')->nullable(); // Array of ticket URLs/QR codes
            $table->timestamp('tickets_issued_at')->nullable();
            
            // Staff tracking (for offline bookings)
            $table->foreignId('created_by_user_id')->nullable()->constrained('users');
            $table->string('booking_source')->default('online'); // online, offline, api
            
            // Notes and metadata
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('reference');
            $table->index('state');
            $table->index('customer_id');
            $table->index('visit_date');
            $table->index('hold_expires_at');
            $table->index('created_at');
            $table->index(['state', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
