<section class="reference-media-section {{ ($reel ?? false) ? 'reference-reel-row' : '' }}" @if($sectionId ?? null) id="{{ $sectionId }}" @endif>
    <div class="reference-section-heading">
        <h2><x-public.icon :name="$icon" />{{ $title }}</h2>
        @if($more ?? null)<a href="{{ $more }}">{{ __('site.ref.view_all') }} <span aria-hidden="true">→</span></a>@endif
    </div>
    <div class="{{ ($reel ?? false) ? 'reference-reel-grid' : 'reference-video-grid' }}">
        @forelse($rowItems as $item)
            <x-public.reference-media-card :item="$item" :reel="$reel ?? false" />
        @empty
            @forelse($samples as $sample)
                <x-public.reference-media-card :sample="$sample" :reel="$reel ?? false" />
            @empty
                <p class="reference-empty">{{ __('site.no_results') }}</p>
            @endforelse
        @endforelse
    </div>
</section>

