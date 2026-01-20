<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            
            // Subject (what was acted upon)
            $table->morphs('subject');                       // subject_type, subject_id
            
            // Actor (who performed the action)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();         // Snapshot for deleted users
            
            // Action details
            $table->string('action');                        // e.g., created, updated, deleted, state_changed
            $table->text('description')->nullable();
            
            // Changes (before/after)
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            // Metadata
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();            // Additional context
            
            $table->timestamps();
            

            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
