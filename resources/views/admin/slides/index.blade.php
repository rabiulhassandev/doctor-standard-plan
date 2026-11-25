@extends('admin.layout')

@section('title', 'Homepage slider')

@section('actions')
    <a href="{{ route('admin.slides.create') }}" class="admin-button admin-button-primary">Add slide</a>
@endsection

@section('intro')
    <p class="mt-3 text-sm admin-muted">Manage the images and videos shown on the homepage. Lower position numbers appear first.</p>
@endsection

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($slides as $slide)
            <article class="overflow-hidden admin-surface">
                @php($preview = $slide->carouselSlide())
                @if($slide->kind === 'image')
                    <img src="{{ $preview['src'] }}" alt="{{ $preview['alt'] }}" class="aspect-video w-full object-cover" loading="lazy">
                @else
                    <video src="{{ $preview['video'] }}" @if($preview['poster']) poster="{{ $preview['poster'] }}" @endif aria-label="{{ $preview['alt'] }}" controls preload="none" class="aspect-video w-full bg-slate-950 object-contain"></video>
                @endif
                <div class="space-y-3 p-4">
                    <div>
                        <h2 class="font-semibold break-words">{{ $slide->title }}</h2>
                        <p class="admin-muted mt-1 flex flex-wrap items-center gap-2 text-sm">{{ ucfirst($slide->kind) }} · Position {{ $slide->sort_order }} <span class="admin-badge" data-status="{{ $slide->is_active ? 'visible' : 'hidden' }}">{{ $slide->is_active ? 'Visible' : 'Hidden' }}</span></p>
                    </div>
                    <a href="{{ route('admin.slides.edit', $slide) }}" class="admin-button admin-button-small">Edit slide</a>
                </div>
            </article>
        @empty
            <p class="admin-surface p-6 text-sm text-slate-600 sm:col-span-2 xl:col-span-3">No slides have been added.</p>
        @endforelse
    </div>
    <div class="mt-5">{{ $slides->links() }}</div>
@endsection
