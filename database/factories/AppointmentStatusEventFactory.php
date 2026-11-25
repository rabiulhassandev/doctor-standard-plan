<?php

namespace Database\Factories;

use App\Models\AppointmentRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentStatusEventFactory extends Factory
{
    public function definition(): array
    {
        return ['appointment_request_id' => AppointmentRequest::factory(), 'to_status' => 'new'];
    }
}
