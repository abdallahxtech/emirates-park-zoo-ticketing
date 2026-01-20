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
            $table->foreignId('role_id')->after('password')->nullable()->constrained('roles')->nullOnDelete();
            $table->foreignId('invited_by')->after('role_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('invited_at')->after('invited_by')->nullable();
            $table->timestamp('last_login_at')->after('invited_at')->nullable();
            $table->enum('status', ['active', 'invited', 'suspended'])->after('last_login_at')->default('active');
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
