@php
    $locale = app()->getLocale();
    $siteName = $settings->text('site_name') ?: __('site.site_name');
    $doctorName = $profile->text('name') ?: __('site.doctor_name');
    $currentRoute = request()->route()?->getName();
    $otherLocale = $locale === 'bn' ? 'en' : 'bn';
    $switchParameters = array_merge(request()->route()?->parameters() ?? [], ['locale' => $otherLocale]);
    $switchUrl = $seo['alternates'][$otherLocale] ?? match ($currentRoute) {
        'public.article' => route('public.articles', ['locale' => $otherLocale]),
        'public.media' => route('public.health', ['locale' => $otherLocale]),
        default => route($currentRoute ?: 'public.home', $switchParameters),
    };
    $libraryShell = in_array($currentRoute, ['public.videos', 'public.reels', 'public.media'], true);
    $analyticsAllowed = in_array($currentRoute, ['public.home', 'public.about', 'public.services', 'public.chambers', 'public.health', 'public.articles', 'public.article', 'public.videos', 'public.reels', 'public.media', 'public.gallery'], true) && preg_match('/^G-[A-Z0-9]+$/', (string) $settings->analytics_id);
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0a1725">
    <title>{{ $seo['title'] ?? $siteName }}</title>
    <meta name="description" content="{{ $seo['description'] ?? $settings->text('seo_description') }}">
    <link rel="canonical" href="{{ $seo['canonical'] ?? url()->current() }}">
    @foreach(($seo['alternates'] ?? []) as $language => $href)<link rel="alternate" hreflang="{{ $language }}" href="{{ $href }}">@endforeach
    @if($seo['noindex'] ?? false)<meta name="robots" content="noindex, follow">@endif
    <meta property="og:title" content="{{ $seo['title'] ?? $siteName }}">
    <meta property="og:description" content="{{ $seo['description'] ?? $settings->text('seo_description') }}">
    <meta property="og:url" content="{{ $seo['canonical'] ?? url()->current() }}">
    <meta property="og:type" content="{{ $currentRoute === 'public.article' ? 'article' : 'website' }}">
    <meta property="og:locale" content="{{ $locale === 'bn' ? 'bn_BD' : 'en_GB' }}">
    @if($seo['image'] ?? null)<meta property="og:image" content="{{ route('images.show', ['path' => $seo['image']]) }}">@endif
    @if(!empty($seo['schema']))<script type="application/ld+json">{!! json_encode($seo['schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>@endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body @class(['public-site reference-shell reference-dark-theme', 'reference-library-shell' => $libraryShell, 'reference-home' => $currentRoute === 'public.home']) @if($analyticsAllowed) data-analytics-id="{{ $settings->analytics_id }}" @endif>
    <a class="skip-link" href="#main-content">{{ __('site.skip_content') }}</a>
    @include('public.partials.reference-nav')
    <main id="main-content" tabindex="-1">@yield('content')</main>
    @include('public.partials.reference-footer')
    <x-public.floating-actions :share-url="$seo['canonical'] ?? url()->current()" :locale="$locale" />
    @if($analyticsAllowed)
        <aside class="consent-banner" data-consent-banner hidden aria-label="{{ __('site.cookie_preferences') }}"><div><strong>{{ __('site.analytics_title') }}</strong><p>{{ __('site.analytics_text') }}</p></div><div class="flex gap-3"><button class="btn btn-secondary" data-consent="denied" type="button">{{ __('site.decline') }}</button><button class="btn btn-primary" data-consent="granted" type="button">{{ __('site.allow_analytics') }}</button></div></aside>
    @endif
    @stack('scripts')
</body>
</html>

