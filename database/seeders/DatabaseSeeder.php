<?php

namespace Database\Seeders;

use App\Models\DoctorProfile;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        User::query()->firstOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Demo Admin',
            'password' => 'password',
            'is_admin' => true,
        ]);

        $configured = DoctorProfile::query()->where('is_demo', false)->exists()
            || SiteSetting::query()->where('is_demo', false)->exists();
        $this->call([$configured ? DemoContentSeeder::class : DemoSeeder::class, DemoMediaSeeder::class]);
    }
}
