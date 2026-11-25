<?php

namespace Database\Factories;

use App\Models\ContentTranslation;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentTranslation>
 */
class ContentTranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'translatable_type' => Service::class,
            'translatable_id' => Service::factory(),
            'locale' => 'en',
            'status' => 'draft',
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'body' => fake()->paragraphs(3, true),
        ];
    }
}
