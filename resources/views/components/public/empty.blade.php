@props(['message' => null])
<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <x-public.icon name="book" />
    <p>{{ $message ?? __('site.empty') }}</p>
</div>
