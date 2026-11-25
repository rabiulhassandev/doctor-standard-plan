@extends('public.layout')

@section('content')
    <x-public.page-heading :eyebrow="__('site.everyday_wellbeing')" :title="__('site.health_library')" :description="__('site.library_description')">
        <nav class="public-page-actions" aria-label="{{ __('site.health_library') }}">
            @foreach(['articles' => 'book', 'videos' => 'play', 'reels' => 'reel'] as $page => $icon)
                <a class="filter-pill" href="{{ route('public.'.$page, ['locale' => $locale]) }}"><x-public.icon :name="$icon" />{{ __('site.nav_'.$page) }}</a>
            @endforeach
        </nav>
    </x-public.page-heading>

    <section class="section">
        <div class="container">
            <div class="public-section-header">
                <h2>{{ __('site.articles_title') }}</h2>
                <a class="btn btn-secondary" href="{{ route('public.articles', ['locale' => $locale]) }}">{{ __('site.all_articles') }}</a>
            </div>
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @forelse($articles as $item)<x-public.article-card :item="$item" />@empty<x-public.empty class="col-span-full" />@endforelse
            </div>
        </div>
    </section>

    <section class="section section-tinted">
        <div class="container">
            <div class="public-section-header">
                <h2>{{ __('site.videos_title') }}</h2>
                <a class="btn btn-secondary" href="{{ route('public.videos', ['locale' => $locale]) }}">{{ __('site.all_videos') }}</a>
            </div>
            <div class="reference-video-grid">
                @forelse($media as $item)<x-public.reference-media-card :item="$item" />@empty<x-public.empty class="col-span-full" />@endforelse
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="public-section-header">
                <h2>{{ __('site.reels_title') }}</h2>
                <a class="btn btn-secondary" href="{{ route('public.reels', ['locale' => $locale]) }}">{{ __('site.all_reels') }}</a>
            </div>
            <div class="reference-reel-grid">
                @forelse($reels as $item)<x-public.reference-media-card :item="$item" reel />@empty<x-public.empty class="col-span-full" />@endforelse
            </div>
        </div>
    </section>
@endsection

