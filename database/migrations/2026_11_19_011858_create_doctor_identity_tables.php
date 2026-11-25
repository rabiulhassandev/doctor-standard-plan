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
        Schema::create('doctor_profiles', function (Blueprint $table): void {
            $table->id();
            $table->json('name')->nullable();
            $table->json('designation')->nullable();
            $table->json('specialty')->nullable();
            $table->json('qualifications')->nullable();
            $table->json('introduction')->nullable();
            $table->json('biography')->nullable();
            $table->string('image_path')->nullable();
            $table->string('registration_number')->nullable();
            $table->unsignedSmallInteger('experience_since')->nullable();
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
        });

        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->json('site_name')->nullable();
            $table->json('tagline')->nullable();
            $table->json('footer_text')->nullable();
            $table->json('seo_title')->nullable();
            $table->json('seo_description')->nullable();
            $table->json('address')->nullable();
            $table->json('privacy_text')->nullable();
            $table->json('appointment_notice')->nullable();
            $table->json('contact_notice')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('whatsapp', 32)->nullable();
            $table->string('email')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('og_image_path')->nullable();
            $table->string('notification_email')->nullable();
            $table->string('facebook_url', 2048)->nullable();
            $table->string('youtube_url', 2048)->nullable();
            $table->string('map_url', 2048)->nullable();
            $table->string('analytics_id', 32)->nullable();
            $table->string('default_locale', 5)->default('bn');
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
        });

        Schema::create('credentials', function (Blueprint $table): void {
            $table->id();
            $table->string('kind', 24)->default('qualification');
            $table->json('title');
            $table->json('institution')->nullable();
            $table->json('description')->nullable();
            $table->unsignedSmallInteger('start_year')->nullable();
            $table->unsignedSmallInteger('end_year')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credentials');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('doctor_profiles');
    }
};
