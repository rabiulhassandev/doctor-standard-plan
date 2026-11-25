@extends('public.layout')

@section('content')
    <x-public.page-heading :eyebrow="__('site.your_information')" :title="__('site.privacy')" :description="__('site.privacy_description')" />

    <section class="section">
        <div class="container public-reading-width">
            <div class="public-reading-panel public-surface prose-copy">
                @if($settings->text('privacy_text'))
                    {!! nl2br(e($settings->text('privacy_text'))) !!}
                @else
                    @foreach(['collect', 'use', 'protect', 'external', 'contact'] as $topic)
                        <h2>{{ __('site.privacy_'.$topic.'_title') }}</h2>
                        <p>
                            {{ __('site.privacy_'.$topic) }}
                            @if($topic === 'contact' && $settings->email)<a href="mailto:{{ $settings->email }}">{{ $settings->email }}</a>@endif
                        </p>
                    @endforeach
                @endif
            </div>
        </div>
    </section>
@endsection

