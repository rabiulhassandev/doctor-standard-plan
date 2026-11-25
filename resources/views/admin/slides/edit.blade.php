@extends('admin.layout')

@section('title', $slide->exists ? 'Edit slide' : 'Add slide')

@section('content')
    <a href="{{ route('admin.slides.index') }}" class="mb-5 inline-block text-sm font-semibold text-teal-700 hover:underline">Back to slider</a>

    <form method="POST" enctype="multipart/form-data" action="{{ $slide->exists ? route('admin.slides.update', $slide) : route('admin.slides.store') }}" class="max-w-3xl space-y-5 admin-surface p-5 sm:p-7">
        @csrf
        @if($slide->exists) @method('PUT') @endif

        <div class="grid gap-5 sm:grid-cols-2">
            @include('admin.partials.field', ['name' => 'title', 'label' => 'Slide name', 'value' => $slide->title])
            @include('admin.partials.field', ['name' => 'kind', 'label' => 'Media type', 'type' => 'select', 'choices' => ['image' => 'Image', 'video' => 'Video'], 'value' => $slide->kind])
        </div>

        @include('admin.partials.field', ['name' => 'media', 'label' => $slide->exists ? 'Replace media' : 'Upload media', 'type' => 'file', 'accept' => 'image/jpeg,image/png,image/webp,video/mp4,video/webm', 'help' => 'JPEG, PNG or WebP up to 5 MB. MP4 or WebM up to 50 MB. Select the matching media type above.'])
        @include('admin.partials.field', ['name' => 'poster', 'label' => 'Video cover image', 'type' => 'file', 'help' => 'Optional. Shown while the video loads.'])
        @if($slide->poster_path)
            @include('admin.partials.field', ['name' => 'remove_poster', 'label' => 'Remove current video cover', 'type' => 'checkbox'])
        @endif

        <div class="grid items-start gap-5 sm:grid-cols-2">
            @include('admin.partials.field', ['name' => 'alt_text[bn]', 'label' => 'Media description in Bangla', 'value' => $slide->alt_text['bn'] ?? ''])
            @include('admin.partials.field', ['name' => 'alt_text[en]', 'label' => 'Media description in English', 'value' => $slide->alt_text['en'] ?? ''])
            @include('admin.partials.field', ['name' => 'sort_order', 'label' => 'Position', 'type' => 'number', 'value' => $slide->sort_order])
            @include('admin.partials.field', ['name' => 'is_active', 'label' => 'Show on the homepage', 'type' => 'checkbox', 'value' => $slide->is_active])
        </div>

        <button type="submit" class="admin-button admin-button-primary">Save slide</button>
    </form>

    @if($slide->exists)
        <details class="mt-5 max-w-3xl admin-surface p-5">
            <summary class="cursor-pointer text-sm font-semibold text-red-700">Delete slide</summary>
            <form method="POST" action="{{ route('admin.slides.destroy', $slide) }}" class="mt-4">
                @csrf @method('DELETE')
                <p class="mb-3 text-sm text-slate-600">This permanently removes the slide and its uploaded media.</p>
                <button type="submit" class="admin-button admin-button-danger">Delete “{{ $slide->title }}”</button>
            </form>
        </details>
    @endif
@endsection
