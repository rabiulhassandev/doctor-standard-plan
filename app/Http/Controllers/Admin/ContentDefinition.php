<?php

namespace App\Http\Controllers\Admin;

use App\Models\Article;
use App\Models\Category;
use App\Models\Chamber;
use App\Models\Credential;
use App\Models\GalleryItem;
use App\Models\MediaItem;
use App\Models\Service;
use App\Models\Testimonial;

class ContentDefinition
{
    /** @return array<string, array<string, mixed>> */
    public static function all(): array
    {
        $publishing = [
            'slug' => ['text', 'URL slug'],
            'status' => ['select', 'Publication', ['draft' => 'Draft', 'published' => 'Published']],
            'sort_order' => ['number', 'Display order'],
            'featured' => ['checkbox', 'Feature on homepage'],
        ];
        $translations = ['title', 'description', 'body', 'seo_title', 'seo_description', 'alt_text'];

        return [
            'services' => ['title' => 'Services', 'model' => Service::class, 'translated' => true, 'localized' => $translations, 'fields' => $publishing],
            'credentials' => ['title' => 'Qualifications & experience', 'model' => Credential::class, 'translated' => false, 'localized' => ['title', 'institution', 'description'], 'fields' => [
                'kind' => ['select', 'Type', ['qualification' => 'Qualification', 'experience' => 'Experience']],
                'start_year' => ['number', 'Start year'], 'end_year' => ['number', 'End year'],
                'sort_order' => ['number', 'Display order'], 'is_active' => ['checkbox', 'Visible'],
            ]],
            'chambers' => ['title' => 'Chambers & visiting hours', 'model' => Chamber::class, 'translated' => false, 'localized' => ['name', 'hospital_name', 'address', 'notice'], 'fields' => [
                'slug' => ['text', 'URL slug'], 'phone' => ['tel', 'Chamber phone'], 'serial_phone' => ['tel', 'Serial / appointment phone'],
                'map_url' => ['url', 'Google Maps link'], 'latitude' => ['text', 'Latitude'], 'longitude' => ['text', 'Longitude'],
                'sort_order' => ['number', 'Display order'], 'is_active' => ['checkbox', 'Accept requests and show publicly'],
            ]],
            'categories' => ['title' => 'Content categories', 'model' => Category::class, 'translated' => false, 'localized' => ['title', 'description'], 'fields' => [
                'kind' => ['select', 'Content type', ['article' => 'Articles', 'media' => 'Videos & reels']], 'slug' => ['text', 'URL slug'],
                'sort_order' => ['number', 'Display order'], 'is_active' => ['checkbox', 'Visible'],
            ]],
            'articles' => ['title' => 'Articles & health tips', 'model' => Article::class, 'translated' => true, 'localized' => $translations, 'fields' => [
                ...$publishing, 'category_id' => ['category', 'Category', 'article'], 'published_at' => ['datetime-local', 'Publish date'],
            ]],
            'media' => ['title' => 'Videos & reels', 'model' => MediaItem::class, 'translated' => true, 'localized' => $translations, 'fields' => [
                ...$publishing, 'kind' => ['select', 'Format', ['video' => 'Video', 'reel' => 'Reel / short']],
                'category_id' => ['category', 'Category', 'media'], 'video_url' => ['url', 'YouTube / Shorts URL'],
                'duration_seconds' => ['number', 'Duration in seconds'], 'published_at' => ['datetime-local', 'Publish date'],
                'consent_at' => ['datetime-local', 'Patient media permission recorded on (when applicable)'],
            ]],
            'gallery' => ['title' => 'Gallery', 'model' => GalleryItem::class, 'translated' => true, 'localized' => ['title', 'description', 'alt_text'], 'fields' => [
                ...$publishing, 'category' => ['select', 'Category', ['practice' => 'Practice', 'professional' => 'Professional', 'certificate' => 'Certificates', 'award' => 'Awards', 'event' => 'Events']],
                'consent_at' => ['datetime-local', 'Patient media permission recorded on (when applicable)'],
            ]],
            'testimonials' => ['title' => 'Testimonials', 'model' => Testimonial::class, 'translated' => true, 'localized' => ['title', 'description', 'body'], 'fields' => [
                ...$publishing, 'patient_name' => ['text', 'Public display name (use initials when appropriate)'],
                'consent_at' => ['datetime-local', 'Publication permission recorded on'],
            ]],
        ];
    }

    /** @return array<string, mixed> */
    public static function get(string $section): array
    {
        return self::all()[$section] ?? abort(404);
    }
}
