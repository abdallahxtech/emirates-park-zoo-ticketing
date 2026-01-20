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
        Schema::table('bookings', function (Blueprint $table) {
            // Rename booking_source to source if it exists
            if (Schema::hasColumn('bookings', 'booking_source')) {
                $table->renameColumn('booking_source', 'source');
            } else {
                // Otherwise create it
                if (!Schema::hasColumn('bookings', 'source')) {
                    $table->string('source')->default('wordpress');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'source')) {
                $table->renameColumn('source', 'booking_source');
            }
        });
    }
};
