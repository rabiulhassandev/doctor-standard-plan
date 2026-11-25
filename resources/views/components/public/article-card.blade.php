@props(['item'])
<article {{ $attributes->merge(['class' => 'article-card']) }}>
    <a class="content-image" href="{{ route('public.article', ['locale' => app()->getLocale(), 'slug' => $item->slug]) }}" tabindex="-1" aria-hidden="true">
        @if($item->image_path)<img src="{{ route('images.show', ['path' => $item->image_path]) }}" alt="" width="640" height="400" loading="lazy">@else<div class="article-art"><x-public.icon name="{{ $item->id % 2 ? 'heart' : 'book' }}" /></div>@endif
    </a>
    <div class="content-card-body"><div class="content-meta"><span>{{ $item->category?->text('title') ?: __('site.health_tips') }}</span>@if($item->published_at)<time datetime="{{ $item->published_at->toDateString() }}">{{ $item->published_at->locale(app()->getLocale())->translatedFormat('j M Y') }}</time>@endif</div>
        <h3><a href="{{ route('public.article', ['locale' => app()->getLocale(), 'slug' => $item->slug]) }}">{{ $item->text('title') }}</a></h3>
        <p>{{ $item->text('description') }}</p>
        <a class="btn btn-secondary public-article-link" href="{{ route('public.article', ['locale' => app()->getLocale(), 'slug' => $item->slug]) }}" aria-label="{{ __('site.read_article') }}: {{ $item->text('title') }}">{{ __('site.read_article') }}<x-public.icon name="chevron" /></a>
    </div>
</article>
