@extends('public.layout')

@section('content')
    @php($mediaPage = $item->kind === 'reel' ? 'reels' : 'videos')

    <article class="section section-dark public-media-article">
        <div class="container media-detail">
            <nav class="breadcrumbs" aria-label="{{ __('site.breadcrumb') }}">
                <a href="{{ route('public.health', ['locale' => $locale]) }}">{{ __('site.health_library') }}</a>
                <x-public.icon name="chevron" />
                <a href="{{ route('public.'.$mediaPage, ['locale' => $locale]) }}">{{ __('site.nav_'.$mediaPage) }}</a>
            </nav>
            <header>
                <p class="reference-feature-tag">{{ $item->category?->text('title') ?: __('site.health_discussion') }}</p>
                <h1>{{ $item->text('title') }}</h1>
                @if($item->text('description'))<p class="article-lead">{{ $item->text('description') }}</p>@endif
            </header>

            @if($item->provider === 'youtube' && preg_match('/^[A-Za-z0-9_-]{11}$/', (string) $item->provider_id))
                <div class="video-player {{ $item->kind === 'reel' ? 'video-player-portrait' : '' }}" data-video-player data-video-id="{{ $item->provider_id }}" data-video-title="{{ $item->text('title') }}">
                    @if($item->image_path)
                        <img src="{{ route('images.show', ['path' => $item->image_path]) }}" alt="" width="960" height="540">
                    @endif
                    <div class="video-placeholder">
                        <button class="video-load-button reference-btn reference-btn-primary" type="button" data-video-load>
                            <x-public.icon name="play" />{{ __('site.play_video') }}
                        </button>
                        <p>{{ __('site.video_privacy') }}</p>
                    </div>
                </div>
                <p class="small-text muted">{{ __('site.playback_trouble') }} <a class="text-link" href="https://www.youtube.com/watch?v={{ $item->provider_id }}" target="_blank" rel="noopener noreferrer">{{ __('site.open_youtube') }}</a></p>
            @else
                <x-public.empty :message="__('site.video_unavailable')" />
            @endif

            @if($item->text('body'))<div class="prose-copy mt-5">{!! nl2br(e($item->text('body'))) !!}</div>@endif
            <p class="content-disclaimer">{{ __('site.educational_disclaimer') }}</p>
        </div>
    </article>

    <x-public.cta :whatsapp="$settings->whatsapp" />
@endsection

