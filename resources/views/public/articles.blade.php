@extends('public.layout')

@section('content')
    <x-public.page-heading :eyebrow="__('site.health_library')" :title="__('site.articles_title')" :description="__('site.articles_description')">
        <nav class="public-page-actions" aria-label="{{ __('site.health_library') }}">
            <a class="filter-pill" aria-current="page" href="{{ route('public.articles', ['locale' => $locale]) }}">{{ __('site.nav_articles') }}</a>
            <a class="filter-pill" href="{{ route('public.videos', ['locale' => $locale]) }}">{{ __('site.nav_videos') }}</a>
            <a class="filter-pill" href="{{ route('public.reels', ['locale' => $locale]) }}">{{ __('site.nav_reels') }}</a>
        </nav>
    </x-public.page-heading>

    <section class="section">
        <div class="container">
            <form class="library-filter public-surface" role="search" method="get" action="{{ route('public.articles', ['locale' => $locale]) }}">
                <div class="field">
                    <label for="library-search">{{ __('site.search_library') }}</label>
                    <input class="input" id="library-search" name="q" type="search" value="{{ request('q') }}" maxlength="100" placeholder="{{ __('site.search_placeholder') }}">
                </div>
                <div class="field">
                    <label for="library-category">{{ __('site.category') }}</label>
                    <select class="input" name="category" id="library-category">
                        <option value="">{{ __('site.all_categories') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->text('title') }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-primary" type="submit"><x-public.icon name="search" />{{ __('site.search') }}</button>
                @if(request()->anyFilled(['q', 'category']))<a class="btn btn-secondary" href="{{ route('public.articles', ['locale' => $locale]) }}">{{ __('site.clear_filters') }}</a>@endif
            </form>
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @forelse($items as $item)
                    <x-public.article-card :item="$item" />
                @empty
                    <x-public.empty class="col-span-full" :message="__('site.no_results')" />
                @endforelse
            </div>
            <div class="pagination">{{ $items->withQueryString()->links() }}</div>
        </div>
    </section>
@endsection

