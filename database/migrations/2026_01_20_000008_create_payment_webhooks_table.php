<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->nullable()->constrained()->cascadeOnDelete();
            
            $table->string('webhook_id')->unique();          // From gateway or generated
            $table->string('event_type');                    // e.g., payment.completed
            
            // Idempotency
            $table->string('idempotency_key')->unique();     // Prevents duplicate processing
            $table->boolean('processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            
            // Webhook data
            $table->json('payload');                         // Full webhook payload
            $table->text('signature')->nullable();
            $table->boolean('signature_valid')->default(false);
            
            // Processing result
            $table->enum('status', ['pending', 'processed', 'failed', 'ignored'])->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            
            $table->timestamps();
            
            $table->index('idempotency_key');
            $table->index(['processed', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_webhooks');
    }
};
