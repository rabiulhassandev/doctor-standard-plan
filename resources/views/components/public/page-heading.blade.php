@props(['eyebrow', 'title', 'description' => null])

<section {{ $attributes->class(['page-hero', 'public-page-heading']) }}>
    <div class="container">
        <p class="eyebrow">{{ $eyebrow }}</p>
        <h1>{{ $title }}</h1>
        @if($description)<p>{{ $description }}</p>@endif
        {{ $slot }}
    </div>
</section>

