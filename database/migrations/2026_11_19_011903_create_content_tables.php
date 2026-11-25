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
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('kind', 24);
            $table->string('slug', 180);
            $table->json('title');
            $table->json('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['kind', 'slug']);
        });

        Schema::create('services', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 180)->unique();
            $table->string('status', 24)->default('draft');
            $table->string('image_path')->nullable();
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['status', 'sort_order']);
        });

        Schema::create('articles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug', 180)->unique();
            $table->string('status', 24)->default('draft');
            $table->string('image_path')->nullable();
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'published_at']);
        });

        Schema::create('media_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug', 180)->unique();
            $table->string('kind', 24)->default('video');
            $table->string('provider', 24)->default('youtube');
            $table->string('provider_id', 32);
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('status', 24)->default('draft');
            $table->string('image_path')->nullable();
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('consent_at')->nullable();
            $table->timestamps();
            $table->index(['kind', 'status', 'sort_order']);
        });

        Schema::create('gallery_items', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 180)->unique();
            $table->string('category', 80)->nullable();
            $table->string('status', 24)->default('draft');
            $table->string('image_path')->nullable();
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('consent_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'sort_order']);
        });

        Schema::create('testimonials', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 180)->unique();
            $table->string('patient_name')->nullable();
            $table->string('status', 24)->default('draft');
            $table->string('image_path')->nullable();
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('consent_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'sort_order']);
        });

        Schema::create('content_translations', function (Blueprint $table): void {
            $table->id();
            $table->string('translatable_type', 100);
            $table->unsignedBigInteger('translatable_id');
            $table->string('locale', 5);
            $table->string('slug', 180)->nullable();
            $table->string('status', 24)->default('draft');
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('body')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('alt_text')->nullable();
            $table->timestamps();
            $table->unique(['translatable_type', 'translatable_id', 'locale'], 'content_translation_locale_unique');
            $table->unique(['translatable_type', 'locale', 'slug'], 'content_translation_slug_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_translations');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('gallery_items');
        Schema::dropIfExists('media_items');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('services');
        Schema::dropIfExists('categories');
    }
};
