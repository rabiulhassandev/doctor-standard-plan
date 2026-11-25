@extends('public.layout')

@section('content')
    <article class="public-article">
        <header class="page-hero">
            <div class="container public-reading-width">
                <nav class="breadcrumbs" aria-label="{{ __('site.breadcrumb') }}">
                    <a href="{{ route('public.home', ['locale' => $locale]) }}">{{ __('site.nav_home') }}</a>
                    <x-public.icon name="chevron" />
                    <a href="{{ route('public.articles', ['locale' => $locale]) }}">{{ __('site.nav_articles') }}</a>
                </nav>
                <p class="eyebrow">{{ $item->category?->text('title') ?: __('site.health_tips') }}</p>
                <h1>{{ $item->text('title') }}</h1>
                @if($item->text('description'))<p class="article-lead">{{ $item->text('description') }}</p>@endif
                <div class="article-byline">
                    @if($profile->text('name'))<span><x-public.icon name="person" />{{ $profile->text('name') }}</span>@endif
                    @if($item->published_at)<time datetime="{{ $item->published_at->toDateString() }}"><x-public.icon name="calendar" />{{ $item->published_at->locale($locale)->translatedFormat('j F Y') }}</time>@endif
                </div>
            </div>
        </header>

        <div class="section">
            <div class="container public-reading-width">
                @if($item->image_path)
                    <img class="article-cover" src="{{ route('images.show', ['path' => $item->image_path]) }}" alt="{{ $item->text('alt_text') }}" width="960" height="600">
                @endif
                <div class="prose-copy article-body">{!! nl2br(e($item->text('body'))) !!}</div>
                <aside class="content-disclaimer"><x-public.icon name="shield" /><p>{{ __('site.educational_disclaimer') }}</p></aside>
                <a class="btn btn-secondary mt-5" href="{{ route('public.articles', ['locale' => $locale]) }}">{{ __('site.all_articles') }}</a>
            </div>
        </div>
    </article>
    <x-public.cta :whatsapp="$settings->whatsapp" />
@endsection

