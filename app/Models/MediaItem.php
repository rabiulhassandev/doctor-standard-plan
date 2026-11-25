<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\MediaItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['slug', 'status', 'sort_order', 'featured', 'image_path', 'category_id', 'published_at', 'kind', 'provider', 'provider_id', 'duration_seconds', 'consent_at'])]
class MediaItem extends Model
{
    /** @use HasFactory<MediaItemFactory> */
    use HasFactory, HasTranslations;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'featured' => 'boolean',
            'published_at' => 'datetime',
            'duration_seconds' => 'integer',
            'consent_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function watchUrl(): string
    {
        return 'https://www.youtube.com/watch?v='.rawurlencode($this->provider_id ?? '');
    }
}
