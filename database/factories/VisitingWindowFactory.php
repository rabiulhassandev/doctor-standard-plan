<?php

namespace Database\Factories;

use App\Models\Chamber;
use App\Models\VisitingWindow;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VisitingWindow>
 */
class VisitingWindowFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chamber_id' => Chamber::factory(),
            'weekday' => fake()->numberBetween(0, 6),
            'starts_at' => '17:00:00',
            'ends_at' => '20:00:00',
            'is_active' => true,
        ];
    }
}
