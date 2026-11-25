<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedFields;
use Database\Factories\HomeSlideFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'kind', 'file_path', 'poster_path', 'alt_text', 'sort_order', 'is_active'])]
class HomeSlide extends Model
{
    /** @use HasFactory<HomeSlideFactory> */
    use HasFactory, HasLocalizedFields;

    protected function casts(): array
    {
        return ['alt_text' => 'array', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    /** @return array{alt: string, src?: string, video?: string, poster?: string|null} */
    public function carouselSlide(): array
    {
        $url = route('home-slides.file', ['homeSlide' => $this, 'file' => 'media']);
        $alt = $this->text('alt_text');

        if ($this->kind === 'image') {
            return ['src' => $url, 'alt' => $alt];
        }

        return [
            'video' => $url,
            'poster' => $this->poster_path ? route('home-slides.file', ['homeSlide' => $this, 'file' => 'poster']) : null,
            'alt' => $alt,
        ];
    }
}
