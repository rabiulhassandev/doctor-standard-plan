<?php

namespace App\Services;

use App\Models\Chamber;
use App\Models\DoctorProfile;
use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PublicSeo
{
    public function forPage(Request $request, string $page, DoctorProfile $profile, SiteSetting $settings, ?Model $item = null): array
    {
        $locale = app()->getLocale();
        $labels = [
            'home' => ['en' => 'Care that starts with listening', 'bn' => 'আপনার সুস্থতাই আমাদের অগ্রাধিকার'],
            'about' => ['en' => 'Meet your doctor', 'bn' => 'আপনার চিকিৎসককে জানুন'],
            'services' => ['en' => 'Services & care', 'bn' => 'সেবা ও চিকিৎসা'],
            'chambers' => ['en' => 'Chambers & visiting hours', 'bn' => 'চেম্বার ও সময়সূচি'],
            'health' => ['en' => 'Health library', 'bn' => 'স্বাস্থ্য জ্ঞান'],
            'articles' => ['en' => 'Health articles', 'bn' => 'স্বাস্থ্য পরামর্শ'],
            'videos' => ['en' => 'Health discussions', 'bn' => 'স্বাস্থ্য আলোচনা'],
            'reels' => ['en' => 'One minute for your health', 'bn' => 'এক মিনিটে স্বাস্থ্যকথা'],
            'gallery' => ['en' => 'Around the practice', 'bn' => 'চেম্বারের কিছু মুহূর্ত'],
            'contact' => ['en' => 'Get in touch', 'bn' => 'যোগাযোগ'],
            'appointment' => ['en' => 'Request an appointment', 'bn' => 'অ্যাপয়েন্টমেন্টের অনুরোধ'],
            'privacy' => ['en' => 'Your privacy', 'bn' => 'আপনার গোপনীয়তা'],
        ];
        $title = $item?->text('seo_title') ?: ($item?->text('title') ?: ($page === 'home' ? $settings->text('seo_title') : ''));
        $title = $title ?: ($labels[$page][$locale] ?? '');
        $name = $profile->text('name') ?: $settings->text('site_name');
        $canonical = $request->url();
        $alternates = [];
        foreach (['en', 'bn'] as $language) {
            if (! $item || $item->newQuery()->publishedIn($language)->whereKey($item->id)->exists()) {
                $alternates[$language] = route($request->route()->getName(), [...$request->route()->parameters(), 'locale' => $language]);
            }
        }
        $schema = [];
        if (! $profile->is_demo && $name) {
            $schema[] = ['@context' => 'https://schema.org', '@type' => 'Person', '@id' => route('public.about', ['locale' => $locale]).'#doctor', 'name' => $name, 'jobTitle' => $profile->text('designation'), 'url' => route('public.about', ['locale' => $locale])];
            if (in_array($page, ['home', 'chambers'], true)) {
                foreach (Chamber::where('is_active', true)->with(['visitingWindows' => fn ($query) => $query->where('is_active', true)])->get() as $chamber) {
                    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    $schema[] = [
                        '@context' => 'https://schema.org', '@type' => 'Physician',
                        '@id' => route('public.chambers', ['locale' => $locale]).'#'.$chamber->slug,
                        'name' => $name.' — '.$chamber->text('name'),
                        'url' => route('public.chambers', ['locale' => $locale]),
                        'address' => ['@type' => 'PostalAddress', 'streetAddress' => $chamber->text('address'), 'addressCountry' => 'BD'],
                        'telephone' => $chamber->serial_phone ?: $chamber->phone,
                        'openingHoursSpecification' => $chamber->visitingWindows->map(fn ($window) => [
                            '@type' => 'OpeningHoursSpecification', 'dayOfWeek' => 'https://schema.org/'.$days[$window->weekday],
                            'opens' => substr($window->starts_at, 0, 5), 'closes' => substr($window->ends_at, 0, 5),
                        ])->all(),
                    ];
                }
            }
        }
        if ($item && $page === 'article') {
            $schema[] = ['@context' => 'https://schema.org', '@type' => 'BlogPosting', 'headline' => $item->text('title'), 'description' => $item->text('description'), 'datePublished' => $item->published_at?->toIso8601String(), 'dateModified' => $item->updated_at->toIso8601String(), 'inLanguage' => $locale, 'mainEntityOfPage' => $canonical, 'author' => ['@type' => 'Person', 'name' => $name, 'url' => route('public.about', ['locale' => $locale])]];
        }
        if ($item && $page === 'media' && $item->published_at && preg_match('/^[A-Za-z0-9_-]{11}$/', $item->provider_id ?? '')) {
            $schema[] = [
                '@context' => 'https://schema.org', '@type' => 'VideoObject',
                'name' => $item->text('title'), 'description' => $item->text('description'),
                'thumbnailUrl' => $item->image_path ? route('images.show', ['path' => $item->image_path]) : 'https://i.ytimg.com/vi/'.$item->provider_id.'/hqdefault.jpg',
                'uploadDate' => $item->published_at->toIso8601String(),
                'embedUrl' => 'https://www.youtube-nocookie.com/embed/'.$item->provider_id,
            ];
        }
        if ($page !== 'home') {
            $schema[] = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => $locale === 'bn' ? 'হোম' : 'Home', 'item' => route('public.home', ['locale' => $locale])],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $title, 'item' => $canonical],
            ]];
        }

        return ['title' => trim($title.($name ? ' · '.$name : '')), 'description' => $item?->text('seo_description') ?: ($item?->text('description') ?: $settings->text('seo_description')), 'canonical' => $canonical, 'alternates' => $alternates, 'schema' => $schema, 'image' => $item?->image_path ?: $settings->og_image_path, 'noindex' => $request->routeIs('public.contact*', 'public.appointment*') || $request->hasAny(['category', 'q']) || $profile->is_demo || $settings->is_demo];
    }
}
