@extends('public.layout')

@section('content')
    <x-public.page-heading :eyebrow="__('site.areas_of_care')" :title="__('site.services_title')" :description="__('site.services_description')" />

    <section class="section reference-services-list">
        <div class="container">
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @forelse($services as $service)
                    <x-public.service-card :service="$service" :index="$loop->index" />
                @empty
                    <x-public.empty class="md:col-span-2 lg:col-span-3" />
                @endforelse
            </div>
            <p class="content-disclaimer">{{ __('site.services_disclaimer') }}</p>
        </div>
    </section>

    <x-public.cta consultation :whatsapp="$settings->whatsapp" />

    <section class="section reference-contact-band">
        <div class="container">
            <p class="eyebrow">{{ __('site.get_in_touch') }}</p>
            <h2>{{ __('site.ref.visit_or_call') }}</h2>
            <a class="btn btn-secondary" href="{{ route('public.contact', ['locale' => $locale]) }}">{{ __('site.ref.chamber_contact') }}</a>
        </div>
    </section>
@endsection

