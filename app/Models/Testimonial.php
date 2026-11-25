<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['slug', 'status', 'sort_order', 'featured', 'image_path', 'patient_name', 'consent_at'])]
class Testimonial extends Model
{
    /** @use HasFactory<TestimonialFactory> */
    use HasFactory, HasTranslations;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'featured' => 'boolean',
            'consent_at' => 'datetime',
        ];
    }
}
