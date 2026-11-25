@extends('admin.layout')
@section('title', 'Doctor profile')
@section('intro')<p class="mt-3 text-sm admin-muted">Your professional identity and biography, available in both languages.</p>@endsection
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.profile.update') }}" class="space-y-7">@csrf @method('PUT')
<section class="admin-surface p-6"><h2 class="mb-6 text-lg font-semibold">Professional details</h2><div class="grid gap-6 sm:grid-cols-2">
@include('admin.partials.field', ['name' => 'registration_number', 'label' => 'BMDC / professional registration number', 'type' => 'text', 'value' => $profile->registration_number, 'help' => 'Use verified information approved for public display.'])
@include('admin.partials.field', ['name' => 'experience_since', 'label' => 'Practicing since (year)', 'type' => 'number', 'value' => $profile->experience_since, 'help' => ''])
<div class="sm:col-span-2">@include('admin.partials.field', ['name' => 'image', 'label' => 'Doctor photograph', 'type' => 'file', 'help' => 'JPEG, PNG or WebP, up to 5 MB and 4000 × 4000 pixels. Use a clear professional photograph.'])@if($profile->image_path)<img src="{{ route('images.show', ['path' => $profile->image_path]) }}" alt="Current doctor photo" class="mt-4 size-32 rounded-xl object-cover">@endif</div>
</div></section>
<div class="grid items-start gap-6 xl:grid-cols-2">@foreach(['bn' => 'বাংলা', 'en' => 'English'] as $locale => $label)<section lang="{{ $locale }}" class="admin-surface p-6"><h2 class="mb-6 text-lg font-semibold">{{ $label }} profile</h2><div class="space-y-5">@foreach(['name' => 'Doctor name', 'designation' => 'Designation', 'specialty' => 'Specialty', 'qualifications' => 'Qualifications summary', 'introduction' => 'Short introduction', 'biography' => 'Full biography'] as $field => $fieldLabel)
@include('admin.partials.field', ['name' => $field.'['.$locale.']', 'label' => $fieldLabel, 'type' => in_array($field, ['biography', 'introduction', 'qualifications']) ? 'textarea' : 'text', 'value' => $profile->{$field}[$locale] ?? '', 'help' => '', 'rows' => $field === 'biography' ? 10 : 4])
@endforeach</div></section>@endforeach</div>
<button class="admin-button admin-button-primary" type="submit">Save profile</button>
</form>
@endsection

