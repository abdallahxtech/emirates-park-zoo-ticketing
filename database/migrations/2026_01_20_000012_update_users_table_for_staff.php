<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the simple string role column if it exists
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
            
            // Add proper staff management fields
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            }
            
            if (!Schema::hasColumn('users', 'invited_by')) {
                $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('users', 'invited_at')) {
                $table->timestamp('invited_at')->nullable();
            }

            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'status')) {
                 $table->enum('status', ['active', 'invited', 'suspended'])->default('active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['invited_by']);
            $table->dropColumn(['role_id', 'invited_by', 'invited_at', 'last_login_at', 'status']);
            
            // Restore simple role column
            $table->string('role')->default('sales')->after('password');
        });
    }
};
