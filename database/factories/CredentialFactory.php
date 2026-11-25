<?php

namespace Database\Factories;

use App\Models\Credential;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Credential>
 */
class CredentialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => ['en' => 'Example qualification', 'bn' => 'নমুনা যোগ্যতা'],
            'institution' => ['en' => 'Example institution', 'bn' => 'নমুনা প্রতিষ্ঠান'],
            'kind' => 'qualification',
            'is_active' => false,
            'sort_order' => 0,
        ];
    }
}
