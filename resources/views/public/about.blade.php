@extends('public.layout')

@section('content')
    @php($showPortrait = $profile->image_path || $profile->is_demo)
    <section class="page-hero">
        <div class="container public-profile-grid {{ $showPortrait ? 'public-profile-grid-with-photo' : '' }}">
            <div class="public-profile-copy">
                <p class="eyebrow">{{ __('site.about_title') }}</p>
                <h1>{{ $profile->text('name') ?: __('site.doctor_name') }}</h1>
                @if($profile->text('specialty') || $profile->text('designation') || $profile->text('qualifications'))
                    <ul class="public-profile-details">
                        @foreach(['specialty', 'designation', 'qualifications'] as $attribute)
                            @if($profile->text($attribute))<li>{{ $profile->text($attribute) }}</li>@endif
                        @endforeach
                    </ul>
                @endif
                <div class="prose-copy">{!! nl2br(e($profile->text('biography') ?: __('site.about_description'))) !!}</div>
                @if($profile->registration_number)<p class="credential-line"><x-public.icon name="shield" />{{ __('site.registration') }} {{ $profile->registration_number }}</p>@endif
                <div class="public-page-actions">
                    <a class="btn btn-primary" href="{{ route('public.appointment', ['locale' => $locale]) }}">{{ __('site.request_appointment') }}</a>
                    <a class="btn btn-secondary" href="{{ route('public.chambers', ['locale' => $locale]) }}">{{ __('site.nav_chambers') }}</a>
                </div>
            </div>
            @if($showPortrait)
                <div class="public-profile-photo">
                    @if($profile->image_path)
                        <x-public.portrait :profile="$profile" large />
                    @else
                        <x-public.demo-photo :scene="3" :alt="__('site.ref.demo_photo')" />
                    @endif
                </div>
            @endif
        </div>
    </section>

    @if($credentials->isNotEmpty())
        <section class="section">
            <div class="container">
                <x-public.heading :eyebrow="__('site.professional_background')" :title="__('site.qualifications_experience')" />
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach($credentials as $credential)
                        <article class="credential-card public-surface">
                            <span class="icon-box"><x-public.icon name="{{ $credential->kind === 'qualification' ? 'book' : 'stethoscope' }}" /></span>
                            <div class="min-w-0">
                                @if($credential->start_year)
                                    <p class="small-label">{{ $credential->start_year }}@if($credential->end_year) – {{ $credential->end_year }}@endif</p>
                                @endif
                                <h3>{{ $credential->text('title') }}</h3>
                                @if($credential->text('institution'))<p>{{ $credential->text('institution') }}</p>@endif
                                @if($credential->text('description'))<p class="muted">{{ $credential->text('description') }}</p>@endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($testimonials->isNotEmpty())
        <section class="section">
            <div class="container">
                <x-public.heading :title="__('site.testimonials_title')" />
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach($testimonials as $testimonial)
                        <figure class="testimonial public-surface">
                            <blockquote>{{ $testimonial->text('body') ?: $testimonial->text('description') }}</blockquote>
                            <figcaption>{{ $testimonial->patient_name }}</figcaption>
                        </figure>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-public.cta :whatsapp="$settings->whatsapp" />
@endsection

