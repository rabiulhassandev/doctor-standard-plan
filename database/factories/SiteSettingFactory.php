<?php

namespace Database\Factories;

use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteSetting>
 */
class SiteSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_name' => ['en' => 'Demo Doctor Practice', 'bn' => 'ডেমো চিকিৎসকের চেম্বার'],
            'default_locale' => 'bn',
            'notification_email' => 'demo@example.com',
            'is_demo' => true,
        ];
    }
}
