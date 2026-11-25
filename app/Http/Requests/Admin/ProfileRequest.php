<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $rules = [
            'registration_number' => ['nullable', 'string', 'max:100'],
            'experience_since' => ['nullable', 'integer', 'between:1900,'.now()->year],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=4000,max_height=4000'],
        ];
        foreach (['name', 'designation', 'specialty', 'qualifications', 'introduction', 'biography'] as $field) {
            $rules[$field] = ['required', 'array:bn,en'];
            foreach (['bn', 'en'] as $locale) {
                $rules[$field.'.'.$locale] = [$field === 'name' ? 'required' : 'nullable', 'string', $field === 'biography' ? 'max:30000' : 'max:3000'];
            }
        }

        return $rules;
    }
}
