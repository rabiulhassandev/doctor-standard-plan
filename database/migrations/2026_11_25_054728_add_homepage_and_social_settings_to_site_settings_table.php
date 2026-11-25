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
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('instagram_url', 2048)->nullable();
            $table->string('linkedin_url', 2048)->nullable();
            $table->string('tiktok_url', 2048)->nullable();
            $table->json('home_notice')->nullable();
            $table->boolean('home_slider_managed')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['instagram_url', 'linkedin_url', 'tiktok_url', 'home_notice', 'home_slider_managed']);
        });
    }
};
