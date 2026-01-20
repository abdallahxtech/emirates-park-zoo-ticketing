<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_id')->unique();           // e.g., ZOO-2026-00123-01
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_item_id')->constrained()->cascadeOnDelete();
            
            $table->string('qr_code')->unique();             // QR code content
            $table->string('qr_image_path')->nullable();     // Path to QR code image
            
            // Galaxy integration
            $table->string('galaxy_ticket_id')->nullable()->unique();
            $table->json('galaxy_response')->nullable();     // Full response from Galaxy API
            
            // Ticket status
            $table->enum('status', ['issued', 'used', 'expired', 'cancelled'])->default('issued');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            $table->timestamps();
            
            $table->index('qr_code');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
