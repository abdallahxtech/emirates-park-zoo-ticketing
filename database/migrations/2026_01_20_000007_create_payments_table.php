<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            
            // Payment gateway
            $table->string('payment_gateway')->default('cybersource');
            $table->string('transaction_id')->nullable()->unique();
            $table->string('session_id')->nullable();
            
            // Amount
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('AED');
            
            // Status
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded', 'partially_refunded'])->default('pending');
            $table->string('failure_reason')->nullable();
            
            // Gateway response
            $table->json('gateway_request')->nullable();     // Payment initiation data
            $table->json('gateway_response')->nullable();    // Payment result
            
            // Refund tracking
            $table->decimal('refunded_amount', 10, 2)->default(0);
            $table->timestamp('refunded_at')->nullable();
            
            // Verification
            $table->boolean('signature_verified')->default(false);
            $table->string('webhook_signature')->nullable();
            
            $table->timestamps();
            
            $table->index('transaction_id');
            $table->index('status');
            $table->index(['booking_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
