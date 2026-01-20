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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('type')->default('abandoned_cart'); // abandonment, inquiry, offline
            $table->string('source')->nullable(); // web, whatsapp, manual
            $table->string('status')->default('new'); // new, contacted, converted, lost
            $table->text('notes')->nullable();
            $table->json('cart_data')->nullable(); // Snapshot of what they were buying
            $table->decimal('potential_value', 10, 2)->default(0);
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users');
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('converted_booking_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
