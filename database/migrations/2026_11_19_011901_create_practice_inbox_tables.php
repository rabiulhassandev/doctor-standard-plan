<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_requests', function (Blueprint $table): void {
            $table->id();
            $table->uuid('reference')->unique();
            $table->string('name', 120);
            $table->string('phone', 20)->index();
            $table->foreignId('chamber_id')->nullable()->constrained()->restrictOnDelete();
            $table->date('preferred_date')->nullable();
            $table->string('preferred_period', 20)->default('no_preference');
            $table->string('locale', 2);
            $table->string('status', 20)->default('new')->index();
            $table->text('notes')->nullable();
            $table->string('notification_status', 20)->default('pending');
            $table->timestamp('notified_at')->nullable();
            $table->uuid('submission_token')->unique();
            $table->timestamps();
            $table->index(['chamber_id', 'created_at']);
        });
        Schema::create('appointment_status_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->text('note')->nullable();
            $table->timestamps();
        });
        Schema::create('inquiries', function (Blueprint $table): void {
            $table->id();
            $table->uuid('reference')->unique();
            $table->string('name', 120);
            $table->string('reply_to');
            $table->text('message');
            $table->string('locale', 2);
            $table->string('status', 20)->default('new')->index();
            $table->string('notification_status', 20)->default('pending');
            $table->timestamp('notified_at')->nullable();
            $table->uuid('submission_token')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
        Schema::dropIfExists('appointment_status_events');
        Schema::dropIfExists('appointment_requests');
    }
};
