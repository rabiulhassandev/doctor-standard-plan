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
        Schema::create('chambers', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 180)->unique();
            $table->json('name');
            $table->json('hospital_name')->nullable();
            $table->json('address');
            $table->json('notice')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('serial_phone', 32)->nullable();
            $table->string('map_url', 2048)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('visiting_windows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('chamber_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->json('notice')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['chamber_id', 'weekday', 'starts_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visiting_windows');
        Schema::dropIfExists('chambers');
    }
};
