@props(['shareUrl', 'locale'])

@php($encodedUrl = rawurlencode($shareUrl))
<div class="floating-actions">
    <details class="floating-share" data-floating-share>
        <summary class="floating-action floating-share-toggle" aria-label="{{ __('site.ref.share_page') }}" title="{{ __('site.ref.share_page') }}">
            <x-public.icon name="share" />
        </summary>

        <div class="floating-share-options">
            <a class="floating-action floating-share-facebook" href="https://www.facebook.com/sharer/sharer.php?u={{ $encodedUrl }}" target="_blank" rel="noopener noreferrer" aria-label="{{ __('site.ref.share_facebook') }}" title="{{ __('site.ref.share_facebook') }}">
                <x-public.icon name="facebook" />
            </a>
            <a class="floating-action floating-share-whatsapp" href="https://wa.me/?text={{ $encodedUrl }}" target="_blank" rel="noopener noreferrer" aria-label="{{ __('site.ref.share_whatsapp') }}" title="{{ __('site.ref.share_whatsapp') }}">
                <x-public.icon name="whatsapp" />
            </a>
            <button class="floating-action floating-share-copy" type="button" data-copy-share aria-label="{{ __('site.ref.copy_link') }}" title="{{ __('site.ref.copy_link') }}" hidden>
                <x-public.icon name="copy" />
            </button>
        </div>

        <div class="floating-share-feedback">
            <p role="status" data-share-status data-copied-message="{{ __('site.ref.link_copied') }}"></p>
            <input type="url" readonly value="{{ $shareUrl }}" aria-label="{{ __('site.ref.copy_link') }}" data-share-url hidden>
        </div>
    </details>

    <a class="floating-appointment" href="{{ route('public.appointment', ['locale' => $locale]) }}#appointment-request">
        <span class="floating-action floating-appointment-icon"><x-public.icon name="person" /></span>
        {{ __('site.ref.serial') }}
    </a>
</div>
