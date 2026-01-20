<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Pages
        if (!Schema::hasTable('pages')) {
            Schema::create('pages', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->json('content')->nullable();
                $table->string('hero_image')->nullable();
                $table->string('seo_title')->nullable();
                $table->text('seo_description')->nullable();
                $table->boolean('is_published')->default(false);
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
            });
        }

        // 2. Posts
        if (!Schema::hasTable('posts')) {
            Schema::create('posts', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('excerpt')->nullable();
                $table->longText('content')->nullable();
                $table->string('featured_image')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->boolean('is_featured')->default(false);
                $table->foreignId('author_id')->constrained('users');
                $table->timestamps();
            });
        }

        // 3. System Settings
        if (!Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $table) {
                $table->id();
                $table->string('brand_name')->default('Emirates Park Zoo');
                $table->string('primary_color')->default('#00A651');
                $table->string('logo_path')->nullable();
                $table->text('welcome_message')->nullable();
                $table->timestamps();
            });
        }

        // 4. Tickets
        if (!Schema::hasTable('tickets')) {
            Schema::create('tickets', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('type')->default('general');
                $table->decimal('price', 10, 2);
                $table->string('currency', 3)->default('AED');
                $table->integer('daily_capacity')->nullable();
                $table->integer('min_quantity')->default(1);
                $table->integer('max_quantity')->default(10);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 5. Leads
        if (!Schema::hasTable('leads')) {
            Schema::create('leads', function (Blueprint $table) {
                $table->id();
                $table->string('customer_name')->nullable();
                $table->string('customer_email')->nullable();
                $table->string('customer_phone')->nullable();
                $table->string('type')->default('abandoned_cart');
                $table->string('source')->nullable();
                $table->string('status')->default('new');
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
    }

    public function down(): void
    {
        // Don't drop in safety net migration
    }
};
