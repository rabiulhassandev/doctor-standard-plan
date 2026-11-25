<?php

namespace Database\Factories;

use App\Models\MediaItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaItem>
 */
class MediaItemFactory extends Factory
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
            'kind' => 'video',
            'provider' => 'youtube',
            'provider_id' => 'aqz-KE-bpKQ',
            'duration_seconds' => 120,
            'published_at' => now(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => 'published'])
            ->afterCreating(function (MediaItem $record): void {
                foreach (['en', 'bn'] as $locale) {
                    $record->translations()->create([
                        'locale' => $locale,
                        'status' => 'published',
                        'title' => 'Example MediaItem',
                        'description' => 'Example content for automated tests.',
                        'body' => 'Example content for automated tests.',
                    ]);
                }
            });
    }
}
