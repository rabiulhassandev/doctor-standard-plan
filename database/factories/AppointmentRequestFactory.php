<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AppointmentRequestFactory extends Factory
{
    public function definition(): array
    {
        return ['reference' => (string) Str::uuid(), 'name' => fake()->name(), 'phone' => '+8801712345678', 'locale' => 'en', 'status' => 'new', 'preferred_period' => 'no_preference', 'notification_status' => 'pending', 'submission_token' => (string) Str::uuid()];
    }
}
