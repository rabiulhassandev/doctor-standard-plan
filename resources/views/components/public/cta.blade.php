@props(['consultation' => false, 'whatsapp' => null])

<section class="cta-section">
    <div class="container cta-inner">
        <div class="cta-copy">
            <h2>{{ __($consultation ? 'site.ref.consultation_title' : 'site.cta_title') }}</h2>
            <p>{{ __($consultation ? 'site.ref.consultation_text' : 'site.cta_text') }}</p>
        </div>
        <div class="cta-actions">
            @if($consultation)
                <a class="btn btn-primary" href="{{ route('public.chambers', ['locale' => app()->getLocale()]) }}">{{ __('site.ref.chamber_details') }}</a>
            @else
                <a class="btn btn-primary" href="{{ route('public.appointment', ['locale' => app()->getLocale()]) }}"><x-public.icon name="calendar" />{{ __('site.request_appointment') }}</a>
            @endif
            @if($whatsapp)
                <a class="btn btn-secondary" href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}" target="_blank" rel="noopener noreferrer"><x-public.icon name="whatsapp" />WhatsApp</a>
            @else
                <a class="btn btn-secondary" href="{{ route('public.contact', ['locale' => app()->getLocale()]) }}"><x-public.icon name="phone" />{{ __('site.ref.chamber_contact') }}</a>
            @endif
        </div>
    </div>
</section>

