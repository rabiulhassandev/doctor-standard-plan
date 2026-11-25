@extends('admin.layout')
@section('title', ($record->exists ? 'Edit ' : 'Add ').strtolower($definition['title']))
@section('intro')<a href="{{ route('admin.content.index', $section) }}" class="admin-link mt-2 inline-block">← Back to {{ strtolower($definition['title']) }}</a>@endsection
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $record->exists ? route('admin.content.update', [$section, $record->id]) : route('admin.content.store', $section) }}" class="space-y-7">
@csrf
@if($record->exists)@method('PUT')@endif
<section class="admin-surface p-5 sm:p-7"><h2 class="mb-6 text-lg font-semibold">Details & visibility</h2><div class="grid gap-5 sm:grid-cols-2">
@foreach($definition['fields'] as $name => $field)
    @php
        $fieldChoices = $field[2] ?? [];
        $fieldType = $field[0];
        $fieldValue = $record->{$name};
        if ($fieldType === 'category') {
            $fieldType = 'select';
            $fieldChoices = ['' => 'No category'] + $categories->where('kind', $field[2])->mapWithKeys(fn($category) => [$category->id => $category->text('title', 'en')])->all();
        }
        if ($field[0] === 'datetime-local') {
            $fieldValue = $record->{$name}?->timezone('Asia/Dhaka')->format('Y-m-d\TH:i');
        }
        if ($name === 'video_url') { $fieldValue = $record->provider_id ? 'https://www.youtube.com/watch?v='.$record->provider_id : ''; }
        if (!$record->exists && in_array($name, ['is_active', 'sort_order', 'status'])) { $fieldValue = match($name) { 'is_active' => true, 'sort_order' => 0, default => 'draft' }; }
    @endphp
    @include('admin.partials.field', ['name' => $name, 'label' => $field[1], 'type' => $fieldType, 'value' => $fieldValue, 'choices' => $fieldChoices, 'help' => $name === 'slug' ? 'Use lowercase English letters, numbers and hyphens. Keep published URLs stable.' : ''])
@endforeach
@if($definition['translated'])
<div class="sm:col-span-2">@include('admin.partials.field', ['name' => 'image', 'label' => $section === 'media' ? 'Custom video thumbnail' : 'Image', 'type' => 'file', 'help' => 'JPEG, PNG or WebP. Maximum 5 MB and 4000 × 4000 pixels.'])@if($record->image_path)<img src="{{ route('images.show', ['path' => $record->image_path]) }}" alt="Current content image" class="mt-4 h-28 w-auto rounded-lg border border-slate-200 object-contain">@endif</div>
@endif
</div>
@if(in_array($section, ['testimonials', 'media', 'gallery']))<p class="mt-6 rounded-lg bg-amber-50 p-4 text-sm leading-relaxed text-amber-950">Publish only material you have permission to use. Obtain and record permission for identifiable patients. Use authentic statements and remove withdrawn content promptly.</p>@endif
@if($section === 'media')<p class="mt-4 text-sm leading-relaxed admin-muted">A translated title does not translate the spoken video. Note the spoken language and caption availability in each description.</p>@endif
</section>

<div class="grid items-start gap-6 xl:grid-cols-2">
@foreach(['bn' => 'বাংলা', 'en' => 'English'] as $locale => $localeName)
@php $translation = $definition['translated'] && $record->exists ? $record->translations->firstWhere('locale', $locale) : null; @endphp
<section class="admin-surface p-5 sm:p-7" lang="{{ $locale }}"><h2 class="mb-6 text-lg font-semibold">{{ $localeName }} content</h2><div class="space-y-5">
    @if($definition['translated'])
        @include('admin.partials.field', ['name' => 'translations['.$locale.'][status]', 'label' => 'Language edition', 'type' => 'select', 'choices' => ['draft' => 'Draft / incomplete', 'published' => 'Published'], 'value' => $translation?->status ?? 'draft', 'help' => 'A published edition needs a title. Incomplete editions stay hidden.'])
    @endif
    @foreach($definition['localized'] as $field)
        @include('admin.partials.field', ['name' => $definition['translated'] ? 'translations['.$locale.']['.$field.']' : $field.'['.$locale.']', 'label' => ucfirst(str_replace('_', ' ', $field)), 'type' => in_array($field, ['body', 'description', 'address', 'notice']) ? 'textarea' : 'text', 'value' => $definition['translated'] ? ($translation?->{$field} ?? '') : ($record->{$field}[$locale] ?? ''), 'help' => $field === 'body' ? 'Use clear paragraphs. Content is displayed as plain text for safe, readable publishing.' : '', 'rows' => $field === 'body' ? 9 : 4])
    @endforeach
</div></section>
@endforeach
</div>

@if($section === 'chambers')
<section class="admin-surface p-5 sm:p-7"><h2 class="text-lg font-semibold">Visiting windows</h2><p class="mt-2 mb-6 text-sm leading-relaxed admin-muted">Local time in Bangladesh. Use multiple rows for separate sessions on the same day. Empty rows are ignored. Add any temporary changes to the bilingual notice above.</p>
@php
    $savedWindows = $record->exists ? $record->visitingWindows->map(fn($window) => ['weekday' => $window->weekday, 'starts_at' => substr($window->starts_at, 0, 5), 'ends_at' => substr($window->ends_at, 0, 5)])->all() : [];
    $windows = old('windows', $savedWindows);
    $windowCount = max(14, count($windows) + 2);
@endphp
<div class="space-y-4">@for($i = 0; $i < min(28, $windowCount); $i++)<div class="grid gap-3 rounded-lg border border-slate-100 p-3 sm:grid-cols-3">
    @include('admin.partials.field', ['name' => 'windows['.$i.'][weekday]', 'label' => 'Day', 'type' => 'select', 'choices' => ['0' => 'Sunday', '1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday'], 'value' => $windows[$i]['weekday'] ?? ($i % 7), 'help' => ''])
    @include('admin.partials.field', ['name' => 'windows['.$i.'][starts_at]', 'label' => 'From', 'type' => 'time', 'value' => $windows[$i]['starts_at'] ?? '', 'help' => ''])
    @include('admin.partials.field', ['name' => 'windows['.$i.'][ends_at]', 'label' => 'Until', 'type' => 'time', 'value' => $windows[$i]['ends_at'] ?? '', 'help' => ''])
</div>@endfor</div>
</section>
@endif
<div class="flex flex-wrap items-center gap-4"><button type="submit" class="admin-button admin-button-primary">Save changes</button><a href="{{ route('admin.content.index', $section) }}" class="px-4 py-3 text-sm text-slate-600">Cancel</a></div>
</form>
@if($record->exists)
@if($definition['translated'])<div class="mt-6 flex flex-wrap gap-4 text-sm">@foreach(['bn' => 'বাংলা', 'en' => 'English'] as $previewLocale => $previewLabel)<a href="{{ route('admin.content.preview', [$section, $record->id, $previewLocale]) }}" target="_blank" rel="noopener" class="font-medium text-teal-800 underline">Preview {{ $previewLabel }}<span class="sr-only"> (opens in a new tab)</span></a>@endforeach</div>@endif
<form method="POST" action="{{ route('admin.content.destroy', [$section, $record->id]) }}" class="mt-12 rounded-xl border border-slate-200 p-5">@csrf @method('DELETE')<h2 class="font-semibold">Archive this content</h2><p class="mt-2 text-sm admin-muted">Remove it from the public website while keeping its saved content and history.</p><label class="mt-4 flex items-center gap-3 text-sm"><input type="checkbox" name="confirm" value="1" required class="size-5 accent-slate-700">I want to archive this item.</label><button class="mt-4 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium" type="submit">Archive content</button></form>
@endif
@endsection

