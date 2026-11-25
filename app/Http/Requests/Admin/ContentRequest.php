<?php

namespace App\Http\Requests\Admin;

use App\Http\Controllers\Admin\ContentDefinition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    protected function prepareForValidation(): void
    {
        $definition = ContentDefinition::get($this->route('section'));
        $flags = [];
        foreach ($definition['fields'] as $name => $field) {
            if ($field[0] === 'checkbox') {
                $flags[$name] = $this->boolean($name);
            }
        }
        $this->merge($flags);
        if (array_key_exists('slug', $definition['fields'])) {
            $model = $definition['model'];
            $record = $model::find((int) $this->route('record'));
            if ($record && ($record->status === 'published' || ! $this->filled('slug'))) {
                $this->merge(['slug' => $record->slug]);
            } elseif (! $this->filled('slug')) {
                $title = $this->input($definition['translated'] ? 'translations.en.title' : ($this->route('section') === 'chambers' ? 'name.en' : 'title.en'));
                $base = is_string($title) ? Str::slug(Str::limit($title, 110, '')) : '';
                $this->merge(['slug' => ($base ?: $this->route('section')).'-'.strtolower(Str::random(8))]);
            }
        }
        if ($this->route('section') === 'chambers') {
            $this->merge(['windows' => array_values(array_filter((array) $this->input('windows', []), fn (mixed $window): bool => is_array($window) && (! empty($window['starts_at']) || ! empty($window['ends_at']))))]);
        }
        if ($this->route('section') === 'media') {
            $input = $this->input('video_url');
            $url = is_string($input) ? parse_url($input) : false;
            $host = strtolower($url['host'] ?? '');
            $id = '';
            if (($url['scheme'] ?? '') === 'https') {
                if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
                    $id = trim($url['path'] ?? '', '/');
                } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
                    parse_str($url['query'] ?? '', $query);
                    $id = $query['v'] ?? '';
                    if (preg_match('~^/(?:shorts|embed)/([a-zA-Z0-9_-]{11})/?$~', $url['path'] ?? '', $matches)) {
                        $id = $matches[1];
                    }
                }
            }
            $this->merge(['provider_id' => is_string($id) ? $id : '']);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $section = $this->route('section');
        $definition = ContentDefinition::get($section);
        $model = new $definition['model'];
        $rules = [
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=4000,max_height=4000'],
        ];
        foreach ($definition['fields'] as $field => $options) {
            $rules[$field] = match ($field) {
                'slug' => ['required', 'string', 'max:160', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique($model->getTable(), 'slug')->ignore((int) $this->route('record'))],
                'status' => ['required', Rule::in(['draft', 'published'])],
                'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
                'featured', 'is_active' => ['required', 'boolean'],
                'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('kind', $options[2])],
                'kind' => ['required', Rule::in(array_keys($options[2]))],
                'category' => ['required', Rule::in(array_keys($options[2]))],
                'map_url' => ['nullable', 'url:https', 'max:1000', 'regex:~^https://(?:(?:www\\.)?google\\.(?:com|com\\.bd)/maps(?:[/?]|$)|maps\\.app\\.goo\\.gl/|goo\\.gl/maps/)~i'],
                'phone', 'serial_phone' => ['nullable', 'string', 'max:30', 'regex:/^[+0-9\\s()-]+$/u'],
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'start_year', 'end_year' => ['nullable', 'integer', 'between:1900,2100'],
                'duration_seconds' => ['nullable', 'integer', 'min:1', 'max:86400'],
                'video_url' => ['required', 'url:https', 'max:1000'],
                'published_at', 'consent_at' => ['nullable', 'date'],
                'patient_name' => ['required', 'string', 'max:150'],
                default => ['nullable', 'string', 'max:1000'],
            };
        }
        if ($section === 'media') {
            $rules['provider_id'] = ['required', 'regex:/^[a-zA-Z0-9_-]{11}$/'];
        }
        if ($definition['translated']) {
            $rules['translations'] = ['required', 'array:bn,en'];
            foreach (['bn', 'en'] as $locale) {
                $rules['translations.'.$locale] = ['required', 'array:status,'.implode(',', $definition['localized'])];
                $rules['translations.'.$locale.'.status'] = ['required', Rule::in(['draft', 'published'])];
                foreach ($definition['localized'] as $field) {
                    $rules['translations.'.$locale.'.'.$field] = ['nullable', 'string', $field === 'body' ? 'max:50000' : 'max:3000'];
                }
                $rules['translations.'.$locale.'.title'] = ['required_if:translations.'.$locale.'.status,published', 'nullable', 'string', 'max:200'];
            }
        } else {
            foreach ($definition['localized'] as $field) {
                $rules[$field] = ['required', 'array:bn,en'];
                $rules[$field.'.bn'] = ['nullable', 'string', 'max:3000'];
                $rules[$field.'.en'] = ['nullable', 'string', 'max:3000'];
            }
            $titleField = $section === 'chambers' ? 'name' : 'title';
            $rules[$titleField.'.bn'] = ['required', 'string', 'max:200'];
            $rules[$titleField.'.en'] = ['required', 'string', 'max:200'];
        }
        if ($section === 'chambers') {
            $rules += [
                'windows' => ['array', 'max:28'],
                'windows.*' => ['array:weekday,starts_at,ends_at'],
                'windows.*.weekday' => ['required', 'integer', 'between:0,6'],
                'windows.*.starts_at' => ['required', 'date_format:H:i'],
                'windows.*.ends_at' => ['required', 'date_format:H:i'],
            ];
        }

        return $rules;
    }

    /** @return array<callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $section = $this->route('section');
            if ($this->input('status') === 'published' && ! collect($this->input('translations', []))->contains(fn (mixed $value): bool => is_array($value) && ($value['status'] ?? '') === 'published')) {
                $validator->errors()->add('status', 'Publish at least one complete language edition.');
            }
            if ($section === 'testimonials' && $this->input('status') === 'published' && ! $this->filled('consent_at')) {
                $validator->errors()->add('consent_at', 'Record permission before publishing a testimonial.');
            }
            if ($section === 'gallery' && $this->input('status') === 'published' && ! $this->hasFile('image')) {
                $model = ContentDefinition::get($section)['model'];
                if (! $model::find((int) $this->route('record'))?->image_path) {
                    $validator->errors()->add('image', 'Upload an image before publishing.');
                }
            }
            if ($section === 'media' && $validator->errors()->has('provider_id')) {
                $validator->errors()->add('video_url', 'Enter a valid YouTube or Shorts HTTPS link.');
            }
            if ($section === 'credentials' && $this->filled('start_year') && $this->filled('end_year') && $this->integer('end_year') < $this->integer('start_year')) {
                $validator->errors()->add('end_year', 'The end year must follow the start year.');
            }
            if ($section === 'chambers' && ! $validator->errors()->hasAny(['windows', 'windows.*.starts_at', 'windows.*.ends_at'])) {
                $windows = $this->input('windows', []);
                foreach ($windows as $index => $window) {
                    if (($window['ends_at'] ?? '') <= ($window['starts_at'] ?? '')) {
                        $validator->errors()->add('windows.'.$index.'.ends_at', 'End time must be later on the same day. Split overnight hours into separate days.');
                    }
                    foreach (array_slice($windows, 0, $index) as $other) {
                        if (($other['weekday'] ?? null) == ($window['weekday'] ?? null) && ($window['starts_at'] ?? '') < ($other['ends_at'] ?? '') && ($window['ends_at'] ?? '') > ($other['starts_at'] ?? '')) {
                            $validator->errors()->add('windows.'.$index.'.starts_at', 'Hours overlap another window on this day.');
                        }
                    }
                }
            }
        }];
    }
}
