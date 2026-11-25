<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kind' => 'article',
            'slug' => fake()->unique()->slug(),
            'title' => ['en' => 'Health awareness', 'bn' => 'স্বাস্থ্য সচেতনতা'],
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
