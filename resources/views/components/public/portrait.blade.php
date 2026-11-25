@props(['profile', 'large' => false])
<div {{ $attributes->class(['doctor-portrait', 'doctor-portrait-large' => $large]) }}>
    @if($profile?->image_path)
        <img src="{{ route('images.show', ['path' => $profile->image_path]) }}" alt="{{ $profile->text('name') }}" width="640" height="720" @if($large) fetchpriority="high" @else loading="lazy" @endif>
    @else
        <div class="portrait-placeholder">
            <div class="portrait-symbol"><x-public.icon name="stethoscope" /></div>
            <p class="portrait-name">{{ $profile?->text('name') ?: __('site.doctor_name') }}</p>
            <p class="portrait-specialty">{{ $profile?->text('specialty') ?: __('site.your_health') }}</p>
            <span class="portrait-caption">{{ __('site.profile_placeholder') }}</span>
        </div>
    @endif
    <div class="portrait-mark" aria-hidden="true"><x-public.icon name="cross" /></div>
</div>
