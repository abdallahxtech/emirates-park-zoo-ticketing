<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_invitations', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('token')->unique();               // Secure random token
            
            $table->string('role')->default('sales');        // Default role for invitation
            $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
            
            // Status
            $table->enum('status', ['pending', 'accepted', 'expired'])->default('pending');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('expires_at');                 // 7 days from creation
            
            // Resulting user
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            
            $table->index('token');
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_invitations');
    }
};
