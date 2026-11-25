@extends('public.layout')

@section('content')
    <x-public.page-heading :eyebrow="__('site.get_in_touch')" :title="__('site.contact_title')" :description="__('site.contact_description')" />

    <section class="section public-form-section">
        <div class="container form-layout">
            <div class="form-panel">
                @include('public.partials.form-feedback')
                <header class="public-form-heading">
                    <span class="icon-box"><x-public.icon name="mail" /></span>
                    <div>
                        <h2>{{ __('site.send_message') }}</h2>
                        <p>{{ $settings->text('contact_notice') ?: __('site.contact_notice') }}</p>
                    </div>
                </header>
                <form action="{{ route('public.contact.store', ['locale' => $locale]) }}" method="post" data-public-form>
                    @csrf
                    <input type="hidden" name="submission_token" value="{{ $submissionToken }}">
                    <div class="honeypot" aria-hidden="true">
                        <label for="contact-website">Website</label>
                        <input id="contact-website" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="field">
                            <label for="name">{{ __('site.full_name') }} <span aria-hidden="true">*</span></label>
                            <input class="input" id="name" name="name" value="{{ old('name') }}" required maxlength="100" autocomplete="name" @error('name') aria-invalid="true" aria-describedby="contact-name-error" @enderror>
                            @error('name')<p class="error" id="contact-name-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="reply_to">{{ __('site.reply_channel') }} <span aria-hidden="true">*</span></label>
                            <input class="input" id="reply_to" name="reply_to" type="text" value="{{ old('reply_to') }}" required maxlength="254" autocomplete="off" @error('reply_to') aria-invalid="true" aria-describedby="contact-reply-error" @enderror>
                            @error('reply_to')<p class="error" id="contact-reply-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field sm:col-span-2">
                            <label for="message">{{ __('site.your_message') }} <span aria-hidden="true">*</span></label>
                            <textarea class="input" id="message" name="message" rows="5" required maxlength="1500" @error('message') aria-invalid="true" aria-describedby="contact-message-error" @enderror>{{ old('message') }}</textarea>
                            @error('message')<p class="error" id="contact-message-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    @include('public.partials.form-privacy')
                    <button class="btn btn-primary form-submit" type="submit"><x-public.icon name="mail" />{{ __('site.send_message') }}</button>
                </form>
            </div>

            <aside class="form-aside">
                <span class="icon-box"><x-public.icon name="phone" /></span>
                <h2>{{ __('site.contact_directly') }}</h2>
                <div class="public-contact-list">
                    @if($settings->phone)
                        <a class="contact-method" href="tel:{{ preg_replace('/[^+0-9]/', '', $settings->phone) }}"><x-public.icon name="phone" /><span><small>{{ __('site.phone_number') }}</small><strong>{{ $settings->phone }}</strong></span></a>
                    @endif
                    @if($settings->email)
                        <a class="contact-method" href="mailto:{{ $settings->email }}"><x-public.icon name="mail" /><span><small>{{ __('site.email_address') }}</small><strong>{{ $settings->email }}</strong></span></a>
                    @endif
                    @if($settings->whatsapp)
                        <a class="contact-method" href="https://wa.me/{{ preg_replace('/\D/', '', $settings->whatsapp) }}" target="_blank" rel="noopener noreferrer"><x-public.icon name="whatsapp" /><span><small>WhatsApp</small><strong>{{ __('site.message_whatsapp') }}</strong></span></a>
                    @endif
                    <a class="contact-method" href="{{ route('public.chambers', ['locale' => $locale]) }}"><x-public.icon name="pin" /><span><small>{{ __('site.visiting_in_person') }}</small><strong>{{ __('site.chamber_schedule') }}</strong></span></a>
                    @if($settings->text('address'))
                        <div class="contact-method"><x-public.icon name="pin" /><span><strong>{!! nl2br(e($settings->text('address'))) !!}</strong>
                            @if($settings->map_url)<a class="text-link" href="{{ $settings->map_url }}" target="_blank" rel="noopener noreferrer">{{ __('site.ref.location') }}</a>@endif
                        </span></div>
                    @endif
                </div>
                <p class="public-aside-note">{{ __('site.emergency_note') }}</p>
            </aside>
        </div>
    </section>
@endsection

