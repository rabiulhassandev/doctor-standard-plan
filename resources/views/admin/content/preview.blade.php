@extends('admin.layout')
@section('title', 'Content preview')
@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-950"><p>This saved edition is visible to the practice owner. Publication status {{ $record->status }} · {{ strtoupper($locale) }}</p><a href="{{ route('admin.content.edit', [$section, $record->id]) }}" class="underline">Back to editor</a></div>
<article lang="{{ $locale }}" class="mx-auto max-w-3xl admin-surface p-6 sm:p-10">
    @if($record->image_path)<img src="{{ route('images.show', ['path' => $record->image_path]) }}" alt="{{ $translation?->alt_text ?? '' }}" class="mb-8 max-h-96 w-full rounded-xl object-contain">@endif
    <h2 class="text-3xl leading-relaxed font-semibold">{{ $translation?->title ?: 'Untitled edition' }}</h2>
    @if($translation?->description)<p class="mt-5 whitespace-pre-line text-lg leading-loose text-slate-600">{{ $translation->description }}</p>@endif
    @if($translation?->body)<div class="mt-8 whitespace-pre-line leading-loose">{{ $translation->body }}</div>@endif
    @if($section === 'media')<a href="https://www.youtube.com/watch?v={{ $record->provider_id }}" target="_blank" rel="noopener" class="admin-button admin-button-primary mt-6">Open source video<span class="sr-only"> (opens in a new tab)</span></a>@endif
</article>
@endsection
