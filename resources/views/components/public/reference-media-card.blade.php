@props(['item' => null, 'sample' => null, 'reel' => false, 'group' => 'discussion'])
@php
    $title = $item?->text('title') ?: ($sample['title'] ?? '');
    $description = $item?->text('description') ?: ($sample['description'] ?? '');
    $href = $item ? route('public.media', ['locale' => app()->getLocale(), 'slug' => $item->slug]) : null;
    $image = $item?->image_path
        ? route('images.show', ['path' => $item->image_path])
        : ($item && $item->provider === 'youtube' && preg_match('/^[A-Za-z0-9_-]{11}$/', (string) $item->provider_id)
            ? 'https://i.ytimg.com/vi/'.$item->provider_id.'/hqdefault.jpg'
            : null);
    $duration = $item?->duration_seconds ? sprintf('%02d:%02d', intdiv($item->duration_seconds, 60), $item->duration_seconds % 60) : null;
@endphp
<article class="reference-media-card {{ $reel ? 'reference-media-reel' : '' }}" data-media-group="{{ $group }}">
    @if($href)<a class="reference-media-thumb" href="{{ $href }}" aria-label="{{ __('site.watch') }} {{ $title }}">@else<div class="reference-media-thumb">@endif
        @if($image)<img src="{{ $image }}" alt="" width="640" height="360" loading="lazy" referrerpolicy="no-referrer">
        @elseif($sample)<x-public.demo-photo :scene="$sample['image']" />
        @else<div class="reference-photo-empty"><x-public.icon name="play" /></div>@endif
        @if($href)<span class="reference-media-play"><x-public.icon name="play" /></span>@endif
        @if($duration)<span class="reference-duration">{{ $duration }}</span>@endif
        @if($sample)<span class="reference-duration">{{ __('site.ref.sample') }}</span>@endif
    @if($href)</a>@else</div>@endif
    <h3>@if($href)<a href="{{ $href }}">{{ $title }}</a>@else{{ $title }}@endif</h3>
    @if($description && !$reel)<p>{{ $description }}</p>@endif
</article>

