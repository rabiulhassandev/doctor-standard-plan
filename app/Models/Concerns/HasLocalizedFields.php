<?php

namespace App\Models\Concerns;

trait HasLocalizedFields
{
    public function text(string $field, ?string $locale = null): string
    {
        $values = $this->getAttribute($field);

        if (! is_array($values)) {
            return is_string($values) ? $values : '';
        }

        $locales = array_unique([$locale ?? app()->getLocale(), config('app.fallback_locale', 'en'), 'en', 'bn']);

        foreach ($locales as $language) {
            if (isset($values[$language]) && is_string($values[$language]) && trim($values[$language]) !== '') {
                return $values[$language];
            }
        }

        return '';
    }
}
