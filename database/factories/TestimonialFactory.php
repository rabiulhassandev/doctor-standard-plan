<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
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
            'status' => 'draft',
            'sort_order' => 0,
            'featured' => false,
            'patient_name' => 'Consenting example patient',
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => 'published', 'consent_at' => now()])
            ->afterCreating(function (Testimonial $record): void {
                foreach (['en', 'bn'] as $locale) {
                    $record->translations()->create([
                        'locale' => $locale,
                        'status' => 'published',
                        'title' => 'Example Testimonial',
                        'description' => 'Example content for automated tests.',
                        'body' => 'Example content for automated tests.',
                    ]);
                }
            });
    }
}
