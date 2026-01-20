<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');       // string, integer, boolean, json
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);    // Can be accessed via public API
            $table->timestamps();
            
            $table->index('key');
        });
        
        // Insert default settings
        DB::table('system_settings')->insert([
            [
                'key' => 'booking_hold_timeout_minutes',
                'value' => '15',
                'type' => 'integer',
                'description' => 'Minutes to hold booking before expiration',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'booking_id_prefix',
                'value' => 'ZOO',
                'type' => 'string',
                'description' => 'Prefix for booking IDs',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'tax_rate',
                'value' => '0',
                'type' => 'decimal',
                'description' => 'Tax rate percentage',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'galaxy_mock_mode',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Use mock Galaxy service instead of real integration',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'site_name',
                'value' => 'Emirates Park Zoo',
                'type' => 'string',
                'description' => 'Site name for emails and branding',
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
