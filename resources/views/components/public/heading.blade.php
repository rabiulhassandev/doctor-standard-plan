@props(['eyebrow' => null, 'title', 'description' => null])
<div {{ $attributes->merge(['class' => 'section-heading']) }}>
    @if($eyebrow)<p class="eyebrow">{{ $eyebrow }}</p>@endif
    <h2>{{ $title }}</h2>
    @if($description)<p class="section-description">{{ $description }}</p>@endif
    {{ $slot }}
</div>
