@props(['item'])
<article {{ $attributes->class(['media-card', 'reel-card' => $item->kind === 'reel']) }}>
    <a class="media-thumbnail" href="{{ route('public.media', ['locale' => app()->getLocale(), 'slug' => $item->slug]) }}">
        @if($item->image_path)<img src="{{ route('images.show', ['path' => $item->image_path]) }}" alt="" width="640" height="360" loading="lazy">@else<div class="media-art"><x-public.icon name="{{ $item->kind === 'reel' ? 'heart' : 'stethoscope' }}" /></div>@endif
        <span class="play-icon"><x-public.icon name="play" /></span>
        @if($item->duration_seconds)<span class="duration">{{ floor($item->duration_seconds / 60) }}:{{ str_pad((string) ($item->duration_seconds % 60), 2, '0', STR_PAD_LEFT) }}</span>@endif
        <span class="sr-only">{{ __('site.watch') }} {{ $item->text('title') }}</span>
    </a>
    <div class="content-meta"><span>{{ $item->category?->text('title') ?: __('site.health_discussion') }}</span></div>
    <h3><a href="{{ route('public.media', ['locale' => app()->getLocale(), 'slug' => $item->slug]) }}">{{ $item->text('title') }}</a></h3>
    <p>{{ $item->text('description') }}</p>
</article>
