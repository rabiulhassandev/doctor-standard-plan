<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedFields;
use Database\Factories\SiteSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['site_name', 'tagline', 'footer_text', 'seo_title', 'seo_description', 'address', 'privacy_text', 'appointment_notice', 'contact_notice', 'home_notice', 'phone', 'whatsapp', 'email', 'facebook_url', 'youtube_url', 'instagram_url', 'linkedin_url', 'tiktok_url', 'map_url', 'logo_path', 'og_image_path', 'analytics_id', 'notification_email', 'default_locale', 'is_demo'])]
class SiteSetting extends Model
{
    /** @use HasFactory<SiteSettingFactory> */
    use HasFactory, HasLocalizedFields;

    protected function casts(): array
    {
        return [
            'site_name' => 'array',
            'tagline' => 'array',
            'footer_text' => 'array',
            'seo_title' => 'array',
            'seo_description' => 'array',
            'address' => 'array',
            'privacy_text' => 'array',
            'appointment_notice' => 'array',
            'contact_notice' => 'array',
            'home_notice' => 'array',
            'home_slider_managed' => 'boolean',
            'is_demo' => 'boolean',
        ];
    }

    public static function current(): static
    {
        return static::query()->find(1) ?? (new static)->forceFill(['id' => 1]);
    }

    /** @return array<string, array{label: string, url: string|null}> */
    public function socialLinks(): array
    {
        $links = [
            'whatsapp' => ['label' => 'WhatsApp', 'url' => $this->whatsapp ? 'https://wa.me/'.preg_replace('/\D/', '', $this->whatsapp) : null],
            'facebook' => ['label' => 'Facebook', 'url' => $this->facebook_url],
            'youtube' => ['label' => 'YouTube', 'url' => $this->youtube_url],
            'instagram' => ['label' => 'Instagram', 'url' => $this->instagram_url],
            'linkedin' => ['label' => 'LinkedIn', 'url' => $this->linkedin_url],
            'tiktok' => ['label' => 'TikTok', 'url' => $this->tiktok_url],
        ];

        return array_filter($links, fn (array $link): bool => filled($link['url']));
    }
}
