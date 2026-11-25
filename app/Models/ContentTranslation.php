<?php

namespace App\Models;

use Database\Factories\ContentTranslationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['locale', 'status', 'slug', 'title', 'description', 'body', 'seo_title', 'seo_description', 'alt_text'])]
class ContentTranslation extends Model
{
    /** @use HasFactory<ContentTranslationFactory> */
    use HasFactory;

    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }
}
