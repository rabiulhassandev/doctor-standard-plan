@extends('public.layout')

@section('content')
@php
    $homeRoute = fn (string $page, array $parameters = []) => route('public.'.$page, ['locale' => $locale, ...$parameters]);
    $hasDemoPhotos = $profile->is_demo || $settings->is_demo;
    $slides = collect();
    if ($profile->image_path) {
        $slides->push(['src' => route('images.show', ['path' => $profile->image_path]), 'alt' => $profile->text('name')]);
    }
    foreach ($gallery->where('category', 'professional')->take(3) as $photo) {
        $slides->push(['src' => route('images.show', ['path' => $photo->image_path]), 'alt' => $photo->text('alt_text') ?: $photo->text('title')]);
    }
    if ($slides->isEmpty() && $hasDemoPhotos) {
        foreach ([1, 2, 3] as $scene) {
            $slides->push(['scene' => $scene, 'alt' => __('site.ref.demo_photo')]);
        }
    }
    $slides = $slides->unique(fn ($slide) => $slide['src'] ?? $slide['scene'])->values();
    $aboutPhoto = $slides->first();
    $clinicPhoto = ['src' => asset('images/practice-demo/clinic-reception.jpg'), 'alt' => __('site.ref.clinic_photo')];
    $clinicVideo = ['video' => asset('images/practice-demo/reception-video.mp4'), 'poster' => asset('images/practice-demo/reception-poster.jpg'), 'alt' => __('site.ref.clinic_video')];
    if ($hasDemoPhotos) {
        $slides = collect([$clinicVideo, $clinicPhoto])->concat($slides->take(2));
    } elseif ($slides->count() < 2) {
        $slides = $slides->concat([$clinicPhoto, $clinicVideo])->take(2);
    }
    if ($settings->home_slider_managed || $managedSlides->isNotEmpty()) {
        $slides = $managedSlides->map->carouselSlide();
    }
    $mapUrl = $chambers->firstWhere('map_url')?->map_url ?: $settings->map_url;
    $noticeChamber = $chambers->first(fn ($chamber) => filled($chamber->text('notice')));
    $notice = $settings->text('home_notice') ?: ($noticeChamber ? $noticeChamber->text('name').' — '.$noticeChamber->text('notice') : __('site.ref.default_notice'));
@endphp

<div class="reference-home-notice" data-home-notice>
    <p data-notice-text>{{ $notice }}</p>
    <marquee hidden aria-hidden="true" data-notice-marquee scrollamount="1" scrolldelay="20" truespeed>{{ $notice }}</marquee>
    <button type="button" hidden class="reference-notice-interaction" data-notice-toggle aria-label="{{ __('site.ref.pause_notice') }}" data-pause-label="{{ __('site.ref.pause_notice') }}" data-resume-label="{{ __('site.ref.resume_notice') }}"></button>
</div>

<h1 class="sr-only">{{ $profile->text('name') ?: __('site.doctor_name') }}</h1>
@if($slides->isNotEmpty() || !$settings->home_slider_managed)
    @include('public.partials.home-carousel', ['slides' => $slides])
@endif

<div class="reference-content">
    <div class="reference-section-heading reference-shortcut-heading"><h2 id="home-shortcut-title">{{ __('site.ref.quick_links') }}</h2></div>
    <nav class="reference-shortcuts" aria-labelledby="home-shortcut-title">
        <x-public.shortcut :label="__('site.ref.about')" :description="__('site.ref.about_summary')" icon="person" :href="$homeRoute('about')" />
        <x-public.shortcut :label="__('site.ref.chamber')" :description="__('site.ref.chamber_summary')" icon="hospital" tone="blue" :href="$homeRoute('chambers')" />
        <x-public.shortcut :label="__('site.ref.watch')" :description="__('site.ref.watch_summary')" icon="play" tone="cream" :href="$homeRoute('videos')" />
        <x-public.shortcut :label="__('site.ref.serial')" :description="__('site.ref.serial_summary')" icon="calendar" tone="rose" :href="$homeRoute('appointment')" />
    </nav>
    <nav class="reference-shortcuts reference-contact-shortcuts" aria-label="{{ __('site.get_in_touch') }}">
        @if($settings->whatsapp || $hasDemoPhotos)
            <x-public.shortcut label="WhatsApp" :description="$settings->whatsapp ? __('site.ref.whatsapp_summary') : __('site.ref.demo_link')" icon="whatsapp" tone="whatsapp" :href="$settings->whatsapp ? 'https://wa.me/'.preg_replace('/\D/', '', $settings->whatsapp) : null" :external="(bool) $settings->whatsapp" />
        @endif
        @if($settings->facebook_url || $hasDemoPhotos)
            <x-public.shortcut label="Facebook" :description="$settings->facebook_url ? __('site.ref.facebook_summary') : __('site.ref.demo_link')" icon="facebook" tone="facebook" :href="$settings->facebook_url" :external="(bool) $settings->facebook_url" />
        @endif
        @if($settings->youtube_url || $hasDemoPhotos)
            <x-public.shortcut label="YouTube" :description="$settings->youtube_url ? __('site.ref.youtube_summary') : __('site.ref.demo_link')" icon="youtube" tone="youtube" :href="$settings->youtube_url" :external="(bool) $settings->youtube_url" />
        @endif
        <x-public.shortcut :label="__('site.ref.location')" :description="__('site.ref.location_summary')" icon="pin" tone="map" :href="$mapUrl ?: $homeRoute('chambers')" :external="(bool) $mapUrl" />
    </nav>

    @if($services->isNotEmpty())
        <section class="home-services" aria-labelledby="home-services-title">
            <div class="public-section-header">
                <div>
                    <p class="public-kicker">{{ __('site.areas_of_care') }}</p>
                    <h2 id="home-services-title">{{ __('site.services_title') }}</h2>
                    <p>{{ __('site.services_description') }}</p>
                </div>
                <a class="btn btn-secondary" href="{{ $homeRoute('services') }}">{{ __('site.all_services') }}<x-public.icon name="chevron" /></a>
            </div>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach($services as $service)
                    <x-public.service-card :service="$service" :index="$loop->index" compact />
                @endforeach
            </div>
        </section>
    @endif

    <section class="reference-media-section">
        <div class="reference-section-heading"><h2><x-public.icon name="play" />{{ __('site.ref.video_heading') }}</h2><a href="{{ $homeRoute('videos') }}">{{ __('site.all_videos') }} <span aria-hidden="true">→</span></a></div>
        <nav class="reference-pills" aria-label="{{ __('site.category') }}">
            <a aria-current="page" href="{{ $homeRoute('videos') }}">{{ __('site.all_videos') }}</a>
            @if($hasDemoPhotos && $media->isEmpty())
                <a href="{{ $homeRoute('videos') }}#doctor-discussions">{{ __('site.ref.discussions') }}</a>
                <a href="{{ $homeRoute('videos') }}#patient-stories">{{ __('site.ref.patient_stories') }}</a>
            @else
                @foreach($videoCategories->take(2) as $category)
                    <a href="{{ $homeRoute('videos', ['category' => $category->slug]) }}">{{ $category->text('title') }}</a>
                @endforeach
            @endif
            <a href="{{ $homeRoute('reels') }}">{{ __('site.ref.reels') }}</a>
        </nav>
        <div class="reference-video-grid">
            @forelse($media as $item)
                <x-public.reference-media-card :item="$item" />
            @empty
                @if($hasDemoPhotos)
                    @foreach(array_slice(__('site.ref.discussion_samples'), 0, 3) as $sample)<x-public.reference-media-card :sample="$sample" />@endforeach
                @else
                    <p class="reference-empty">{{ __('site.no_results') }}</p>
                @endif
            @endforelse
        </div>
    </section>

    <section class="reference-media-section">
        <div class="reference-section-heading"><h2><x-public.icon name="reel" />{{ __('site.ref.reels') }}</h2><a href="{{ $homeRoute('reels') }}">{{ __('site.ref.view_all') }} <span aria-hidden="true">→</span></a></div>
        <div class="reference-reel-grid">
            @forelse($reels as $item)
                <x-public.reference-media-card :item="$item" reel />
            @empty
                @if($hasDemoPhotos)
                    @foreach(array_slice(__('site.ref.reel_samples'), 0, 5) as $sample)<x-public.reference-media-card :sample="$sample" reel />@endforeach
                @else
                    <p class="reference-empty">{{ __('site.no_results') }}</p>
                @endif
            @endforelse
        </div>
    </section>

    <section class="reference-about">
        <div class="reference-about-picture">
            @if(isset($aboutPhoto['scene']))<x-public.demo-photo :scene="3" :alt="__('site.ref.demo_photo')" />
            @elseif($aboutPhoto)<img src="{{ $aboutPhoto['src'] }}" alt="{{ $aboutPhoto['alt'] }}" width="480" height="320" loading="lazy">
            @else<div class="reference-photo-empty"><x-public.icon name="person" /></div>@endif
        </div>
        <div class="reference-about-copy">
            <h2>{{ __('site.about_title') }}</h2>
            <p>{{ $profile->text('biography') ? \Illuminate\Support\Str::limit($profile->text('biography'), 245) : __('site.about_description') }}</p>
            <a class="reference-btn reference-btn-outline" href="{{ $homeRoute('about') }}">{{ __('site.more_about') }} <span aria-hidden="true">→</span></a>
        </div>
    </section>

    @if($articles->isNotEmpty() || $testimonials->isNotEmpty() || $gallery->isNotEmpty())
        <details class="reference-more">
            <summary>{{ __('site.ref.more') }}</summary>
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach($articles as $item)<x-public.article-card :item="$item" />@endforeach
                @foreach($testimonials as $testimonial)
                    <figure class="testimonial"><blockquote>{{ $testimonial->text('body') ?: $testimonial->text('description') }}</blockquote><figcaption>{{ $testimonial->patient_name }}</figcaption></figure>
                @endforeach
                @foreach($gallery as $item)
                    <a href="{{ $homeRoute('gallery', ['category' => $item->category]) }}" class="overflow-hidden rounded-xl">
                        <img class="aspect-[4/3] w-full object-cover" src="{{ route('images.show', ['path' => $item->image_path]) }}" alt="{{ $item->text('alt_text') ?: $item->text('title') }}" loading="lazy" width="400" height="300">
                    </a>
                @endforeach
            </div>
        </details>
    @endif
</div>
<x-public.cta :whatsapp="$settings->whatsapp" />
@endsection

