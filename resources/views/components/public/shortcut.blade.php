@props(['label', 'description', 'icon', 'href' => null, 'tone' => 'mint', 'external' => false])
<{{ $href ? 'a' : 'div' }} class="reference-shortcut reference-tone-{{ $tone }}" @if($href) href="{{ $href }}" @endif @if($external) target="_blank" rel="noopener noreferrer" @endif>
    <span class="reference-shortcut-icon"><x-public.icon :name="$icon" /></span>
    <span><strong>{{ $label }}</strong><small>{{ $description }}</small></span>
    @if($href)<span class="reference-shortcut-arrow" aria-hidden="true">→</span>@endif
</{{ $href ? 'a' : 'div' }}>

