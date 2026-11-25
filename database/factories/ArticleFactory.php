<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
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
            'published_at' => now(),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => 'published'])
            ->afterCreating(function (Article $record): void {
                foreach (['en', 'bn'] as $locale) {
                    $record->translations()->create([
                        'locale' => $locale,
                        'status' => 'published',
                        'title' => 'Example Article',
                        'description' => 'Example content for automated tests.',
                        'body' => 'Example content for automated tests.',
                    ]);
                }
            });
    }
}
