<?php

namespace Database\Factories;

use App\Models\Chamber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Chamber>
 */
class ChamberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(),
            'name' => ['en' => 'Demo Chamber', 'bn' => 'ডেমো চেম্বার'],
            'address' => ['en' => 'Example address, Dhaka', 'bn' => 'নমুনা ঠিকানা, ঢাকা'],
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
