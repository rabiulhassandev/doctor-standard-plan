@extends('public.layout')

@section('content')
    <x-public.page-heading :eyebrow="__('site.professional_life')" :title="__('site.nav_gallery')" :description="__('site.gallery_description')" />

    <section class="section">
        <div class="container">
            @if($categories->isNotEmpty())
                <nav class="public-gallery-filters" aria-label="{{ __('site.category') }}">
                    <a class="filter-pill" href="{{ route('public.gallery', ['locale' => $locale]) }}" @if(!request()->filled('category')) aria-current="page" @endif>{{ __('site.all_categories') }}</a>
                    @foreach($categories as $category)
                        <a class="filter-pill" href="{{ route('public.gallery', ['locale' => $locale, 'category' => $category]) }}" @if(request()->string('category')->toString() === $category) aria-current="page" @endif>{{ __('site.gallery_categories.'.$category) }}</a>
                    @endforeach
                </nav>
            @endif
            <div class="gallery-grid">
                @forelse($items as $item)
                    <figure class="gallery-item">
                        @if($item->image_path)
                            <a href="{{ route('images.show', ['path' => $item->image_path]) }}" target="_blank" rel="noopener noreferrer">
                                <img src="{{ route('images.show', ['path' => $item->image_path]) }}" alt="{{ $item->text('alt_text') ?: $item->text('title') }}" width="640" height="480" loading="lazy">
                                <span class="sr-only">{{ __('site.open_image') }}</span>
                            </a>
                        @else
                            <div class="gallery-placeholder"><x-public.icon name="image" /></div>
                        @endif
                        <figcaption>
                            <h2>{{ $item->text('title') }}</h2>
                            @if($item->text('description'))<p>{{ $item->text('description') }}</p>@endif
                        </figcaption>
                    </figure>
                @empty
                    <x-public.empty class="col-span-full" />
                @endforelse
            </div>
            <div class="pagination">{{ $items->withQueryString()->links() }}</div>
        </div>
    </section>
@endsection

