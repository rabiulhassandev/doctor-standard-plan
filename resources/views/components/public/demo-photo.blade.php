@props(['scene', 'alt' => '', 'alignTop' => false])
@php
    [$source, $crop] = match ((int) $scene) {
        1 => ['home', '674 63 373 271'],
        2 => ['library', '659 63 368 305'],
        3 => ['home', '291 1328 265 175'],
        4 => ['library', '691 494 176 112'],
        5 => ['library', '507 773 173 112'],
        6 => ['library', '815 1039 108 175'],
        8 => ['library', '312 1327 239 109'],
        default => ['library', '312 494 176 112'],
    };
@endphp
<svg class="reference-demo-crop" viewBox="{{ $crop }}" preserveAspectRatio="{{ $alignTop ? 'xMidYMin' : 'xMidYMid' }} slice" @if($alt !== '') role="img" aria-label="{{ $alt }}" @else aria-hidden="true" @endif>
    <image href="{{ asset('images/practice-demo/'.$source.'-reference.png') }}" width="1351" height="{{ $source === 'home' ? 1634 : 1630 }}" />
</svg>

