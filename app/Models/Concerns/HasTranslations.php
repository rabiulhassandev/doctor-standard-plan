<?php

namespace App\Models\Concerns;

use App\Models\ContentTranslation;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTranslations
{
    public static function bootHasTranslations(): void
    {
        static::deleting(function (Model $model): void {
            $model->translations()->delete();
        });
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(ContentTranslation::class, 'translatable');
    }

    public function translation(?string $locale = null): ?ContentTranslation
    {
        return $this->translations->first(fn (ContentTranslation $translation): bool => $translation->locale === ($locale ?? app()->getLocale()) && $translation->status === 'published');
    }

    public function text(string $field, ?string $locale = null): string
    {
        $value = $this->translation($locale)?->getAttribute($field);

        return is_string($value) ? $value : '';
    }

    #[Scope]
    protected function publishedIn(Builder $query, ?string $locale = null): void
    {
        $query->with('translations')->where('status', 'published')
            ->whereHas('translations', fn (Builder $translation): Builder => $translation
                ->where('locale', $locale ?? app()->getLocale())
                ->where('status', 'published'));

        if (array_key_exists('published_at', $this->getCasts())) {
            $query->where(fn (Builder $published): Builder => $published->whereNull('published_at')->orWhere('published_at', '<=', now()));
        }
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
