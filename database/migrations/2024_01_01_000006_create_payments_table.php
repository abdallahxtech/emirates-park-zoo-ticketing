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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            
            // CyberSource identifiers
            $table->string('transaction_id')->unique(); // CyberSource transaction ID
            $table->string('reference_number')->nullable(); // CyberSource reference
            $table->string('idempotency_key')->unique(); // Prevent duplicate processing
            
            // Payment details
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('AED');
            $table->string('status')->default('pending'); // pending, accepted, declined, refunded
            $table->string('payment_method')->nullable(); // card type, etc.
            
            // Card details (last 4 digits only)
            $table->string('card_last4', 4)->nullable();
            $table->string('card_type')->nullable(); // visa, mastercard
            
            // Webhook verification
            $table->text('webhook_signature')->nullable();
            $table->timestamp('webhook_received_at')->nullable();
            $table->integer('webhook_attempts')->default(0);
            
            // Request/Response logging
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->json('webhook_payload')->nullable();
            
            // Refund tracking
            $table->string('refund_transaction_id')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->text('refund_reason')->nullable();
            
            // Failure tracking
            $table->string('decline_reason')->nullable();
            $table->text('error_message')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index('transaction_id');
            $table->index('booking_id');
            $table->index('status');
            $table->index('idempotency_key');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
