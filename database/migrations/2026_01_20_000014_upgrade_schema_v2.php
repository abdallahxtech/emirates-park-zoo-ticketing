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
        // 1. Upgrade products table
        Schema::table('products', function (Blueprint $table) {
            $table->json('options_config')->nullable()->after('capacity_per_day')
                ->comment('Configuration for food options, time slots, etc.');
        });

        // 2. Upgrade booking_items table
        Schema::table('booking_items', function (Blueprint $table) {
            $table->string('time_slot')->nullable()->after('visit_date');
            $table->string('food_preference')->nullable()->after('subtotal');
            $table->text('dietary_notes')->nullable()->after('food_preference');
            $table->json('guest_names')->nullable()->after('dietary_notes');
        });

        // 3. Create leads table
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('new')->index(); // new, contacted, converted, abandoned
            $table->string('name')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            
            // UTM & Tracking
            $table->string('source')->nullable(); // organic, google_ads, walk_in
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->text('landing_page_url')->nullable();
            $table->string('ip_address')->nullable();
            
            $table->foreignId('converted_booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            
            $table->timestamps();
        });

        // 4. Create login_logs table
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->nullable(); // For failed login attempts
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('status'); // success, failed
            $table->timestamp('login_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_logs');
        Schema::dropIfExists('leads');

        Schema::table('booking_items', function (Blueprint $table) {
            $table->dropColumn(['time_slot', 'food_preference', 'dietary_notes', 'guest_names']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('options_config');
        });
    }
};
