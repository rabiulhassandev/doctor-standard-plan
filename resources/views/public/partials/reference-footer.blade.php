<footer class="reference-footer">
    <div class="reference-footer-identity">
        <strong>{{ $doctorName }}</strong>
        <p>{{ $settings->text('tagline') ?: __('site.your_health') }}</p>
        @if($settings->text('footer_text'))<p>{{ $settings->text('footer_text') }}</p>@endif
        @if($settings->is_demo || $profile->is_demo)<p class="reference-demo-label">{{ __('site.ref.demo') }}</p>@endif
    </div>
    <nav aria-label="{{ __('site.explore') }}">
        <a href="{{ route('public.appointment', ['locale' => $locale]) }}"><x-public.icon name="calendar" />{{ __('site.request_appointment') }}</a>
        <a href="{{ route('public.chambers', ['locale' => $locale]) }}"><x-public.icon name="pin" />{{ __('site.nav_chambers') }}</a>
        <a href="{{ route('public.videos', ['locale' => $locale]) }}"><x-public.icon name="play" />{{ __('site.nav_videos') }}</a>
    </nav>
    <div class="reference-footer-social">
        <strong>{{ __('site.get_in_touch') }}</strong>
        <div>
            @foreach($settings->socialLinks() as $icon => $link)
                <a class="reference-social-{{ $icon }}" aria-label="{{ $link['label'] }}" href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer"><x-public.icon :name="$icon" /></a>
            @endforeach
        </div>
        <a href="{{ route('public.privacy', ['locale' => $locale]) }}">{{ __('site.privacy') }}</a>
        @if($analyticsAllowed)<button type="button" data-privacy-open>{{ __('site.cookie_preferences') }}</button>@endif
    </div>
</footer>

