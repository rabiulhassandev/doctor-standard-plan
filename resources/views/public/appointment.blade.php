@extends('public.layout')

@section('content')
    <x-public.page-heading :eyebrow="__('site.plan_visit')" :title="__('site.request_appointment')" :description="__('site.appointment_description')" />

    <section class="section public-form-section">
        <div class="container form-layout">
            <div class="form-panel" id="appointment-request">
                @include('public.partials.form-feedback')
                <header class="public-form-heading">
                    <span class="icon-box"><x-public.icon name="calendar" /></span>
                    <div>
                        <h2>{{ __('site.ref.visit_details') }}</h2>
                        <p>{{ __('site.preference_note') }}</p>
                    </div>
                </header>
                <form action="{{ route('public.appointment.store', ['locale' => $locale]) }}" method="post" data-public-form>
                    @csrf
                    <input type="hidden" name="submission_token" value="{{ $submissionToken }}">
                    <div class="honeypot" aria-hidden="true">
                        <label for="appointment-website">Website</label>
                        <input id="appointment-website" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="field">
                            <label for="name">{{ __('site.full_name') }} <span aria-hidden="true">*</span></label>
                            <input class="input" id="name" name="name" value="{{ old('name') }}" required maxlength="100" autocomplete="name" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                            @error('name')<p id="name-error" class="error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="phone">{{ __('site.phone_number') }} <span aria-hidden="true">*</span></label>
                            <input class="input" id="phone" name="phone" type="tel" value="{{ old('phone') }}" required maxlength="25" autocomplete="tel" inputmode="tel" placeholder="01XXXXXXXXX" @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror>
                            @error('phone')<p id="phone-error" class="error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field sm:col-span-2">
                            <label for="chamber_id">{{ __('site.preferred_chamber') }}</label>
                            <select class="input" id="chamber_id" name="chamber_id" @error('chamber_id') aria-invalid="true" aria-describedby="chamber-error" @enderror>
                                <option value="">{{ __('site.choose_chamber') }}</option>
                                @foreach($chambers as $chamber)
                                    <option value="{{ $chamber->id }}" @selected((string) old('chamber_id', request()->integer('chamber') ?: ($chambers->count() === 1 ? $chambers->first()->id : null)) === (string) $chamber->id)>{{ $chamber->text('name') }}</option>
                                @endforeach
                            </select>
                            @error('chamber_id')<p id="chamber-error" class="error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="preferred_date">{{ __('site.preferred_date') }}</label>
                            <input class="input" id="preferred_date" name="preferred_date" type="date" min="{{ now('Asia/Dhaka')->toDateString() }}" value="{{ old('preferred_date') }}" @error('preferred_date') aria-invalid="true" aria-describedby="date-error" @enderror>
                            @error('preferred_date')<p id="date-error" class="error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="preferred_period">{{ __('site.preferred_period') }}</label>
                            <select class="input" id="preferred_period" name="preferred_period" @error('preferred_period') aria-invalid="true" aria-describedby="period-error" @enderror>
                                @foreach(['no_preference', 'morning', 'afternoon', 'evening'] as $period)
                                    <option value="{{ $period }}" @selected(old('preferred_period', 'no_preference') === $period)>{{ __('site.'.$period) }}</option>
                                @endforeach
                            </select>
                            @error('preferred_period')<p id="period-error" class="error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    @include('public.partials.form-privacy')
                    <button class="btn btn-primary form-submit" type="submit"><x-public.icon name="calendar" />{{ __('site.send_request') }}</button>
                    <p class="small-text muted">{{ __('site.no_payment') }}</p>
                </form>
            </div>

            <aside class="form-aside">
                <span class="icon-box"><x-public.icon name="chat" /></span>
                <h2>{{ __('site.what_happens_next') }}</h2>
                <ol class="steps-list"><li>{{ __('site.request_step_1') }}</li><li>{{ __('site.request_step_2') }}</li><li>{{ __('site.request_step_3') }}</li></ol>
                <div class="aside-divider"></div>
                <h3>{{ __('site.prefer_call') }}</h3>
                @if($settings->phone)<a class="contact-phone" href="tel:{{ preg_replace('/[^+0-9]/', '', $settings->phone) }}">{{ $settings->phone }}</a>@endif
                <p>{{ $settings->text('appointment_notice') ?: __('site.appointment_notice') }}</p>
                <a class="btn btn-secondary mt-4" href="{{ route('public.chambers', ['locale' => $locale]) }}">{{ __('site.chamber_schedule') }}</a>
                <p class="public-aside-note">{{ __('site.emergency_note') }}</p>
            </aside>
        </div>
    </section>
@endsection

