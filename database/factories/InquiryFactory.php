<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InquiryFactory extends Factory
{
    public function definition(): array
    {
        return ['reference' => (string) Str::uuid(), 'name' => fake()->name(), 'reply_to' => fake()->safeEmail(), 'message' => 'Please let me know the visiting hours.', 'locale' => 'en', 'status' => 'new', 'notification_status' => 'pending', 'submission_token' => (string) Str::uuid()];
    }
}
