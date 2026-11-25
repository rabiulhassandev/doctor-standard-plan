<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedFields;
use Database\Factories\DoctorProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'designation', 'specialty', 'qualifications', 'introduction', 'biography', 'image_path', 'registration_number', 'experience_since', 'is_demo'])]
class DoctorProfile extends Model
{
    /** @use HasFactory<DoctorProfileFactory> */
    use HasFactory, HasLocalizedFields;

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'designation' => 'array',
            'specialty' => 'array',
            'qualifications' => 'array',
            'introduction' => 'array',
            'biography' => 'array',
            'experience_since' => 'integer',
            'is_demo' => 'boolean',
        ];
    }

    public static function current(): static
    {
        return static::query()->firstOrNew(['id' => 1]);
    }
}
