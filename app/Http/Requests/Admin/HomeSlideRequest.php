<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HomeSlideRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $kind = $this->input('kind');
        $currentSlide = $this->route('homeSlide');
        $uploadRequired = $currentSlide === null || $currentSlide->kind !== $kind;
        $imageRules = ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=4000,max_height=4000'];

        return [
            'title' => ['required', 'string', 'max:150'],
            'kind' => ['required', Rule::in(['image', 'video'])],
            'media' => [
                $uploadRequired ? 'required' : 'nullable',
                ...($kind === 'video' ? ['file', 'mimetypes:video/mp4,video/webm', 'max:51200'] : $imageRules),
            ],
            'poster' => ['exclude_unless:kind,video', 'nullable', ...$imageRules],
            'remove_poster' => ['sometimes', 'boolean'],
            'alt_text' => ['required', 'array:bn,en'],
            'alt_text.bn' => ['required', 'string', 'max:250'],
            'alt_text.en' => ['required', 'string', 'max:250'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
