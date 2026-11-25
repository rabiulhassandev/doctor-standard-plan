@php
    $mediaShell = in_array($currentRoute, ['public.health', 'public.videos', 'public.reels', 'public.media'], true);
    $brandName = \Illuminate\Support\Str::of($doctorName)->before('·')->replaceMatches('/^(?:Dr\.?|ডা[.:ঃ]?)\s+/ui', '')->squish()->explode(' ');
    $monogram = mb_strtoupper(mb_substr($brandName->first() ?? '', 0, 1).($brandName->count() > 1 ? mb_substr($brandName->last(), 0, 1) : ''));
    $subtitle = $mediaShell ? __('site.ref.video_library') : ($profile->text('specialty') ?: ($settings->text('tagline') ?: __('site.your_health')));
    $hotline = $settings->phone ?: ($chambers->firstWhere('serial_phone')?->serial_phone ?: $chambers->firstWhere('phone')?->phone);
    $dialNumber = preg_replace('/[^+0-9]/', '', (string) $hotline);
    $hotlineHref = $dialNumber !== '' ? 'tel:'.$dialNumber : route('public.chambers', ['locale' => $locale]);
    $navigation = [
        'home' => 'home', 'about' => 'person', 'services' => 'stethoscope', 'chambers' => 'hospital',
        'videos' => 'play', 'reels' => 'reel', 'articles' => 'book', 'health' => 'heart',
        'gallery' => 'image', 'contact' => 'mail',
    ];
@endphp
<header class="reference-header">
    <a class="reference-brand" href="{{ route('public.home', ['locale' => $locale]) }}">
        <span class="reference-brand-mark" aria-hidden="true">
            @if($settings->logo_path)
                <img src="{{ route('images.show', ['path' => $settings->logo_path]) }}" alt="" width="40" height="40">
            @else
                {{ $monogram }}
            @endif
        </span>
        <span class="reference-brand-details"><strong>{{ $doctorName }}</strong><small>{{ $subtitle }}</small></span>
    </a>
    <div class="reference-header-actions">
        <a class="reference-hotline" href="{{ $hotlineHref }}" aria-label="{{ $dialNumber !== '' ? __('site.ref.hotline').': '.$hotline : __('site.ref.chamber_contact') }}">
            <x-public.icon name="phone" />
            <span>
                @if($dialNumber !== '')
                    <small>{{ __('site.ref.hotline') }}</small><bdi>{{ $hotline }}</bdi>
                @else
                    {{ __('site.ref.chamber_contact') }}
                @endif
            </span>
        </a>
        <a class="reference-header-appointment reference-btn reference-btn-primary" href="{{ route('public.appointment', ['locale' => $locale]) }}">{{ __('site.request_appointment') }}</a>
        <button type="button" class="reference-menu-toggle" data-menu-toggle aria-expanded="false" aria-controls="mobile-navigation" aria-label="{{ __('site.toggle_menu') }}">
            <span data-menu-open><x-public.icon name="menu" /></span>
            <span data-menu-close><x-public.icon name="close" /></span>
        </button>
    </div>

    <nav id="mobile-navigation" class="reference-mobile-nav" aria-label="{{ __('site.main_navigation') }}" hidden>
        <div class="reference-menu-head">
            <strong>{{ __('site.ref.menu') }}</strong>
            <a class="reference-locale" href="{{ $switchUrl }}" lang="{{ $otherLocale }}"><x-public.icon name="globe" />{{ $otherLocale === 'bn' ? 'বাংলা' : 'English' }}</a>
        </div>
        <div class="reference-menu-links">
            @foreach($navigation as $link => $icon)
                <a href="{{ route('public.'.$link, ['locale' => $locale]) }}" @if($currentRoute === 'public.'.$link || ($link === 'videos' && $currentRoute === 'public.media')) aria-current="page" @endif>
                    <x-public.icon :name="$icon" /><span>{{ __('site.nav_'.$link) }}</span>
                </a>
            @endforeach
        </div>
        <a class="reference-menu-appointment" href="{{ route('public.appointment', ['locale' => $locale]) }}" @if($currentRoute === 'public.appointment') aria-current="page" @endif>
            <x-public.icon name="calendar" />{{ __('site.request_appointment') }}
        </a>
    </nav>
</header>

