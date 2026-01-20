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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // super_admin, admin, sales_staff, viewer
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->json('permissions')->nullable(); // Array of permission strings
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'role_id']);
        });

        // Add role-related fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('employee_id')->nullable()->unique()->after('phone');
            $table->boolean('is_active')->default(true)->after('employee_id');
            $table->timestamp('last_login_at')->nullable();
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('invitation_sent_at')->nullable();
            $table->timestamp('invitation_accepted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['invited_by']);
            $table->dropColumn([
                'phone',
                'employee_id',
                'is_active',
                'last_login_at',
                'invited_by',
                'invitation_sent_at',
                'invitation_accepted_at',
            ]);
        });

        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }
};
