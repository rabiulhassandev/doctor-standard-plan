<?php

namespace Database\Factories;

use App\Models\HomeSlide;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<HomeSlide>
 */
class HomeSlideFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'kind' => 'image',
            'file_path' => 'media/'.Str::uuid().'.webp',
            'alt_text' => ['en' => 'Clinic reception', 'bn' => 'চেম্বারের রিসেপশন'],
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
