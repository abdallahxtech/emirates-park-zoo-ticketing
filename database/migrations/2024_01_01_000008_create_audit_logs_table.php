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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship
            $table->morphs('auditable'); // auditable_type, auditable_id
            
            // Event details
            $table->string('event'); // state_changed, payment_received, ticket_issued, etc.
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            
            // Context
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            // Additional data
            $table->json('metadata')->nullable();
            $table->text('description')->nullable();
            
            $table->timestamp('created_at'); // Immutable, no updated_at

            $table->index(['auditable_type', 'auditable_id']);
            $table->index('event');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
