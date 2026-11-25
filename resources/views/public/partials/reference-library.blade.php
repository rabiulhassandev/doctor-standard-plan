@php
    $homeRoute = fn (string $page, array $parameters = []) => route('public.'.$page, ['locale' => $locale, ...$parameters]);
    $currentCategory = request()->string('category')->toString();
    $query = request()->string('q')->toString();
    $hasFilter = $currentCategory !== '' || $query !== '';
    $hasDemoPhotos = $profile->is_demo || $settings->is_demo;
    $showSamples = $hasDemoPhotos && !$hasFilter;
    $featured = $items->first();
    $slides = $items->getCollection()->take(4)->map(function ($item) {
        $url = $item->image_path ? route('images.show', ['path' => $item->image_path])
            : ($item->provider === 'youtube' && preg_match('/^[A-Za-z0-9_-]{11}$/', (string) $item->provider_id) ? 'https://i.ytimg.com/vi/'.$item->provider_id.'/hqdefault.jpg' : null);

        return $url ? ['src' => $url, 'alt' => $item->text('title')] : null;
    })->filter()->values();
    if ($slides->isEmpty() && $showSamples) {
        $slides = collect([2, 1, 3])->map(fn ($scene) => ['scene' => $scene, 'alt' => __('site.ref.demo_photo')]);
    }
    $videoGroups = $items->getCollection()->groupBy(fn ($item) => $item->category?->text('title') ?: __('site.ref.discussions'));
    $page = $reelsOnly ? 'reels' : 'videos';
@endphp
<section class="reference-hero reference-library-hero">
    <div class="reference-hero-copy">
        <a class="reference-back" href="{{ $homeRoute('home') }}">← {{ __('site.ref.back_to_site') }}</a>
        <span class="reference-feature-tag"><x-public.icon :name="$reelsOnly ? 'reel' : 'play'" />{{ $reelsOnly ? __('site.nav_reels') : __('site.ref.featured') }}</span>
        <h1>{{ $featured?->text('title') ?: __($reelsOnly ? 'site.reels_title' : 'site.ref.library_headline') }}</h1>
        <p>{{ $featured?->text('description') ?: __('site.ref.library_summary') }}</p>
        <div class="reference-hero-actions">
            <a class="reference-btn reference-btn-primary" href="{{ $featured ? $homeRoute('media', ['slug' => $featured->slug]) : '#library-items' }}"><x-public.icon name="play" />{{ $featured ? __('site.watch') : __('site.ref.browse') }}</a>
            <a class="reference-btn reference-btn-outline" href="{{ $homeRoute($reelsOnly ? 'videos' : 'reels') }}">{{ __($reelsOnly ? 'site.all_videos' : 'site.ref.reels') }}</a>
        </div>
    </div>
    <x-public.photo-slider :slides="$slides" :label="__('site.ref.video_library')" />
</section>

<div class="reference-content" id="library-items">
    <div class="reference-library-controls">
        <nav class="reference-pills" aria-label="{{ __('site.category') }}">
            <a href="{{ $homeRoute($page) }}" @if(!$hasFilter) aria-current="page" @endif>{{ __('site.ref.all') }}</a>
            @if($showSamples && !$reelsOnly && $items->isEmpty())
                <a href="#doctor-discussions">{{ __('site.ref.discussions') }}</a>
                <a href="#patient-stories">{{ __('site.ref.patient_stories') }}</a>
                <a href="#special-programs">{{ __('site.ref.special_programs') }}</a>
            @else
                @foreach($categories as $category)
                    <a href="{{ $homeRoute($page, ['category' => $category->slug]) }}" @if($currentCategory === $category->slug) aria-current="page" @endif>{{ $category->text('title') }}</a>
                @endforeach
            @endif
            <a href="{{ $homeRoute($reelsOnly ? 'videos' : 'reels') }}">{{ __($reelsOnly ? 'site.nav_videos' : 'site.ref.reels') }}</a>
        </nav>
        <details class="reference-library-search" @if($query !== '') open @endif>
            <summary><x-public.icon name="search" />{{ __('site.search') }}</summary>
            <form action="{{ $homeRoute($page) }}" method="get">
                @if($currentCategory !== '')<input type="hidden" name="category" value="{{ $currentCategory }}">@endif
                <label class="sr-only" for="reference-video-search">{{ __('site.search_library') }}</label>
                <input id="reference-video-search" name="q" type="search" value="{{ $query }}" maxlength="100" placeholder="{{ __('site.search_placeholder') }}">
                <button type="submit">{{ __('site.search') }}</button>
            </form>
        </details>
    </div>

    @if($reelsOnly)
        @include('public.partials.reference-library-row', ['title' => __('site.reels_title'), 'icon' => 'reel', 'rowItems' => $items, 'samples' => $showSamples ? __('site.ref.reel_samples') : [], 'reel' => true])
    @elseif($videoGroups->isNotEmpty())
        @foreach($videoGroups as $title => $rowItems)
            @include('public.partials.reference-library-row', ['title' => $title, 'icon' => 'play', 'rowItems' => $rowItems, 'samples' => [], 'more' => !$hasFilter && $rowItems->first()?->category ? $homeRoute('videos', ['category' => $rowItems->first()->category->slug]) : null])
        @endforeach
        @if(!$hasFilter && isset($relatedReels) && $relatedReels->isNotEmpty())
            @include('public.partials.reference-library-row', ['title' => __('site.reels_title'), 'icon' => 'reel', 'rowItems' => $relatedReels, 'samples' => [], 'reel' => true, 'more' => $homeRoute('reels')])
        @endif
    @elseif($showSamples)
        @include('public.partials.reference-library-row', ['title' => __('site.ref.discussions'), 'icon' => 'play', 'rowItems' => [], 'samples' => __('site.ref.discussion_samples'), 'sectionId' => 'doctor-discussions'])
        @include('public.partials.reference-library-row', ['title' => __('site.ref.patient_stories'), 'icon' => 'person', 'rowItems' => [], 'samples' => __('site.ref.patient_samples'), 'sectionId' => 'patient-stories'])
        @include('public.partials.reference-library-row', ['title' => __('site.reels_title'), 'icon' => 'reel', 'rowItems' => $relatedReels ?? [], 'samples' => __('site.ref.reel_samples'), 'reel' => true, 'more' => $homeRoute('reels')])
        @include('public.partials.reference-library-row', ['title' => __('site.ref.special_programs'), 'icon' => 'calendar', 'rowItems' => [], 'samples' => __('site.ref.special_samples'), 'sectionId' => 'special-programs'])
    @else
        <p class="reference-empty">{{ __('site.no_results') }}</p>
    @endif

    <div class="pagination">{{ $items->links() }}</div>
</div>

