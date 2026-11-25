<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $rules = [
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[+0-9\\s()-]+$/'],
            'whatsapp' => ['nullable', 'string', 'max:20', 'regex:/^\\+?[0-9]{8,15}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'notification_email' => ['required', 'email', 'max:255'],
            'default_locale' => ['required', Rule::in(['bn', 'en'])],
            'analytics_id' => ['nullable', 'string', 'regex:/^G-[A-Z0-9]{5,20}$/'],
            'facebook_url' => ['nullable', 'url:https', 'max:1000', 'regex:~^https://(?:www\\.)?facebook\\.com/~i'],
            'youtube_url' => ['nullable', 'url:https', 'max:1000', 'regex:~^https://(?:www\\.)?youtube\\.com/~i'],
            'instagram_url' => ['nullable', 'url:https', 'max:1000', 'regex:~^https://(?:www\\.)?instagram\\.com/~i'],
            'linkedin_url' => ['nullable', 'url:https', 'max:1000', 'regex:~^https://(?:[a-z]{2,3}\\.)?linkedin\\.com/~i'],
            'tiktok_url' => ['nullable', 'url:https', 'max:1000', 'regex:~^https://(?:www\\.)?tiktok\\.com/~i'],
            'home_notice' => ['sometimes', 'array:bn,en'],
            'home_notice.bn' => ['nullable', 'string', 'max:350'],
            'home_notice.en' => ['nullable', 'string', 'max:350'],
            'map_url' => ['nullable', 'url:https', 'max:1000', 'regex:~^https://(?:(?:www\\.)?google\\.(?:com|com\\.bd)/maps(?:[/?]|$)|maps\\.app\\.goo\\.gl/|goo\\.gl/maps/)~i'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=4000,max_height=4000'],
            'og_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=4000,max_height=4000'],
        ];
        foreach (['site_name', 'tagline', 'footer_text', 'seo_title', 'seo_description', 'address', 'privacy_text', 'appointment_notice', 'contact_notice'] as $field) {
            $rules[$field] = ['required', 'array:bn,en'];
            foreach (['bn', 'en'] as $locale) {
                $rules[$field.'.'.$locale] = [in_array($field, ['site_name', 'privacy_text'], true) ? 'required' : 'nullable', 'string', 'max:15000'];
            }
        }

        return $rules;
    }
}
