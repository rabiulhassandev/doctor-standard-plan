@props(['service', 'index' => 0, 'compact' => false])
<article {{ $attributes->merge(['class' => 'service-card']) }}>
    <span class="icon-box"><x-public.icon :name="['stethoscope', 'heart', 'shield', 'cross'][$index % 4]" /></span>
    <h3>{{ $service->text('title') }}</h3>
    @if($service->text('description'))<p>{{ $service->text('description') }}</p>@endif
    @if($service->text('body') && (!$compact || !$service->text('description')) && $service->text('body') !== $service->text('description'))
        <div class="prose-copy small-text">{!! nl2br(e($service->text('body'))) !!}</div>
    @endif
</article>
