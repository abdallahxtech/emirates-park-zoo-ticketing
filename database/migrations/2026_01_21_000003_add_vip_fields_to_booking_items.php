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
        Schema::table('booking_items', function (Blueprint $table) {
            // Unify terminology
            if (Schema::hasColumn('booking_items', 'ticket_id')) {
                $table->renameColumn('ticket_id', 'product_id');
            }
            if (Schema::hasColumn('booking_items', 'ticket_name')) {
                $table->renameColumn('ticket_name', 'product_name');
            }
            if (!Schema::hasColumn('booking_items', 'ticket_type')) {
                $table->string('product_type')->nullable(); // general, vip
            }

            // Add VIP features
            $table->string('food_selection')->nullable(); // International, Arabic, etc.
            $table->text('dietary_notes')->nullable();
            $table->time('time_slot')->change()->nullable(); // Ensure nullable
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_items', function (Blueprint $table) {
            $table->renameColumn('product_id', 'ticket_id');
            $table->dropColumn(['food_selection', 'dietary_notes']);
        });
    }
};
