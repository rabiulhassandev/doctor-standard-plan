@props(['slides', 'label', 'alignTop' => false, 'autoplayInterval' => null])
@php($autoplay = $autoplayInterval && count($slides) > 1)
<div class="reference-slider" data-photo-slider role="region" aria-label="{{ $label }}" @if(count($slides) > 1) aria-roledescription="{{ __('site.ref.carousel') }}" @endif @if($autoplay) data-photo-interval="{{ $autoplayInterval }}" @endif>
    @if($autoplay)
        <button type="button" class="reference-video-control reference-slider-rotation" data-photo-rotation hidden data-play-label="{{ __('site.ref.play_slideshow') }}" data-pause-label="{{ __('site.ref.pause_slideshow') }}" aria-label="{{ __('site.ref.pause_slideshow') }}">
            <span data-video-play-icon hidden><x-public.icon name="play" /></span>
            <span data-video-pause-icon><x-public.icon name="pause" /></span>
        </button>
    @endif
    <div class="reference-slider-viewport">
        @forelse($slides as $slide)
            <div data-photo-slide @if(!$loop->first) hidden @endif>
                @if(isset($slide['video']))
                    <video data-slide-video src="{{ $slide['video'] }}" @if($slide['poster']) poster="{{ $slide['poster'] }}" @endif playsinline loop muted preload="none" aria-label="{{ $slide['alt'] }}"></video>
                    <button type="button" class="reference-video-control" data-slide-video-toggle data-play-label="{{ __('site.ref.play_clip') }}" data-pause-label="{{ __('site.ref.pause_clip') }}" aria-label="{{ __('site.ref.play_clip') }}">
                        <span data-video-play-icon><x-public.icon name="play" /></span>
                        <span data-video-pause-icon hidden><x-public.icon name="pause" /></span>
                    </button>
                @elseif(isset($slide['scene']))
                    <x-public.demo-photo :scene="$slide['scene']" :alt="$slide['alt']" :align-top="$alignTop" />
                @else
                    <img src="{{ $slide['src'] }}" alt="{{ $slide['alt'] }}" width="640" height="440" @if($loop->first) fetchpriority="high" @elseif(!$autoplay) loading="lazy" @endif>
                @endif
            </div>
        @empty
            <div class="reference-photo-empty"><x-public.icon name="stethoscope" /></div>
        @endforelse
    </div>
    @if(count($slides) > 1)
        <button type="button" class="reference-slider-arrow reference-slider-prev" data-photo-prev aria-label="{{ __('site.ref.previous') }}"><x-public.icon name="chevron" /></button>
        <button type="button" class="reference-slider-arrow reference-slider-next" data-photo-next aria-label="{{ __('site.ref.next') }}"><x-public.icon name="chevron" /></button>
        <div class="reference-slider-dots">
            @foreach($slides as $slide)
                <button type="button" data-photo-select="{{ $loop->index }}" aria-label="{{ __('site.ref.photo_number', ['number' => $loop->iteration]) }}" aria-current="{{ $loop->first ? 'true' : 'false' }}"></button>
            @endforeach
        </div>
    @endif
</div>

