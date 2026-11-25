<?php

namespace Database\Factories;

use App\Models\DoctorProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DoctorProfile>
 */
class DoctorProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ['en' => 'Demo Doctor', 'bn' => 'ডেমো চিকিৎসক'],
            'designation' => ['en' => 'Example physician profile', 'bn' => 'চিকিৎসকের নমুনা প্রোফাইল'],
            'specialty' => ['en' => 'General medicine', 'bn' => 'সাধারণ চিকিৎসা'],
            'introduction' => ['en' => 'Example content for testing.', 'bn' => 'পরীক্ষার জন্য নমুনা তথ্য।'],
            'is_demo' => true,
        ];
    }
}
