@extends('public.layout')

@section('content')
    <x-public.page-heading :eyebrow="__('site.plan_visit')" :title="__('site.chambers_title')" :description="__('site.chambers_description')" />

    <section class="section">
        <div class="container">
            <div class="visit-note public-visit-note"><x-public.icon name="clock" /><p>{{ __('site.schedule_note') }}</p></div>
            <div class="grid gap-5 {{ $chambers->count() > 1 ? 'lg:grid-cols-2' : 'public-single-chamber' }}">
                @forelse($chambers as $chamber)
                    <x-public.chamber-card :chamber="$chamber" />
                @empty
                    <x-public.empty />
                @endforelse
            </div>
        </div>
    </section>
@endsection

