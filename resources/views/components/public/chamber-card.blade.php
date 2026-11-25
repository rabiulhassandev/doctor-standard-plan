@props(['chamber'])
<article {{ $attributes->merge(['class' => 'chamber-card']) }}>
    <div class="chamber-card-head"><span class="icon-box"><x-public.icon name="pin" /></span><div><h3>{{ $chamber->text('name') }}</h3><p class="muted">{{ $chamber->text('hospital_name') }}</p></div></div>
    <p class="chamber-address">{{ $chamber->text('address') }}</p>
    @if($chamber->serial_phone || $chamber->phone)
        <p class="mb-3 text-sm font-medium"><a class="text-link" href="tel:{{ preg_replace('/[^+0-9]/', '', $chamber->serial_phone ?: $chamber->phone) }}"><bdi>{{ $chamber->serial_phone ?: $chamber->phone }}</bdi></a></p>
    @endif
    <div class="visiting-hours">
        <span class="small-label">{{ __('site.visiting_hours') }}</span>
        @forelse($chamber->visitingWindows->where('is_active', true) as $window)
            <div class="schedule-row"><span>{{ __('site.day_'.$window->weekday) }}</span><strong>{{ \Carbon\Carbon::parse($window->starts_at)->format('g:i A') }} – {{ \Carbon\Carbon::parse($window->ends_at)->format('g:i A') }}</strong></div>
            @if($window->text('notice'))<p class="small-text muted">{{ $window->text('notice') }}</p>@endif
        @empty
            <p class="muted">{{ __('site.call_for_hours') }}</p>
        @endforelse
    </div>
    @if($chamber->text('notice'))<p class="chamber-notice">{{ $chamber->text('notice') }}</p>@endif
    <div class="flex flex-wrap gap-3">
        <a class="btn btn-primary" href="{{ route('public.appointment', ['locale' => app()->getLocale(), 'chamber' => $chamber->id]) }}">{{ __('site.request_appointment') }}</a>
        @if($chamber->serial_phone || $chamber->phone)<a class="btn btn-secondary" href="tel:{{ preg_replace('/[^+0-9]/', '', $chamber->serial_phone ?: $chamber->phone) }}"><x-public.icon name="phone" /><span>{{ __('site.call') }}</span></a>@endif
        @if($chamber->map_url)<a class="text-link" href="{{ $chamber->map_url }}" target="_blank" rel="noopener noreferrer">{{ __('site.directions') }}</a>@endif
    </div>
</article>
