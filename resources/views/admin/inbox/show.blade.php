@extends('admin.layout')
@section('title', $kind === 'appointments' ? 'Appointment request' : 'Contact inquiry')
@section('intro')<a href="{{ route('admin.inbox.index', $kind) }}" class="admin-link mt-2 inline-block">← Back to inbox</a>@endsection
@section('content')
<div class="grid items-start gap-6 xl:grid-cols-3"><section class="admin-surface p-6 xl:col-span-2"><h2 class="text-xl font-semibold">{{ $record->name }}</h2><p class="mt-2 break-all text-xs admin-muted">Reference {{ $record->reference }}</p><dl class="mt-7 grid gap-6 sm:grid-cols-2">
<div><dt class="text-xs font-medium tracking-wide admin-muted uppercase">Contact</dt><dd class="mt-2 font-medium">{{ $kind === 'appointments' ? $record->phone : $record->reply_to }}</dd></div>
<div><dt class="text-xs font-medium tracking-wide admin-muted uppercase">Received</dt><dd class="mt-2">{{ $record->created_at->timezone('Asia/Dhaka')->format('d M Y, g:i a') }}</dd></div>
<div><dt class="text-xs font-medium tracking-wide admin-muted uppercase">Language</dt><dd class="mt-2">{{ $record->locale === 'bn' ? 'বাংলা' : 'English' }}</dd></div>
@if($kind === 'appointments')
<div><dt class="text-xs font-medium tracking-wide admin-muted uppercase">Chamber</dt><dd class="mt-2">{{ $record->chamber?->text('name', 'en') ?: 'To be arranged' }}</dd></div>
<div><dt class="text-xs font-medium tracking-wide admin-muted uppercase">Preferred date</dt><dd class="mt-2">{{ $record->preferred_date?->format('d M Y') ?: 'No preference' }}</dd></div>
<div><dt class="text-xs font-medium tracking-wide admin-muted uppercase">Preferred period</dt><dd class="mt-2 capitalize">{{ str_replace('_', ' ', $record->preferred_period ?: 'No preference') }}</dd></div>
@else
<div class="sm:col-span-2"><dt class="text-xs font-medium tracking-wide admin-muted uppercase">Message</dt><dd class="mt-3 whitespace-pre-line text-sm leading-relaxed">{{ $record->message }}</dd></div>
@endif
</dl></section>
<section class="admin-surface p-6"><h2 class="mb-6 font-semibold">Manage request</h2><form action="{{ route('admin.inbox.update', [$kind, $record->id]) }}" method="POST" class="space-y-5">@csrf @method('PUT')<input type="hidden" name="expected_status" value="{{ $record->status }}">
@include('admin.partials.field', ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'choices' => array_combine($statuses, array_map('ucfirst', $statuses)), 'value' => $record->status, 'help' => $kind === 'appointments' ? 'Confirm after contacting the patient and agreeing a visit.' : ''])
@if($kind === 'appointments')@include('admin.partials.field', ['name' => 'notes', 'label' => 'Internal coordination notes', 'type' => 'textarea', 'value' => $record->notes, 'help' => 'Keep notes brief and logistical. Avoid clinical records or sensitive medical details.'])@endif
<button type="submit" class="admin-button admin-button-primary w-full">Save update</button></form>
<div class="mt-7 border-t border-slate-100 pt-6"><p class="text-xs font-medium tracking-wide admin-muted uppercase">Notification delivery</p><p class="mt-2 text-sm capitalize">{{ $record->notification_status }}@if($record->notified_at) · {{ $record->notified_at->timezone('Asia/Dhaka')->format('d M, g:i a') }}@endif</p>@if($record->notification_status !== 'sent')<form method="POST" action="{{ route('admin.inbox.retry', [$kind, $record->id]) }}" class="mt-4">@csrf<button class="admin-button admin-button-small" type="submit">Retry alert email</button></form>@endif</div></section></div>
@if($kind === 'appointments')<section class="mt-7 admin-surface p-6"><h2 class="mb-5 text-lg font-semibold">Status history</h2><ol class="space-y-5">@forelse($record->events->sortByDesc('id') as $event)<li class="border-l-2 border-teal-200 pl-4"><p class="text-sm font-medium capitalize">{{ $event->from_status ?: 'Received' }} → {{ $event->to_status }}</p><p class="mt-1 text-xs admin-muted">{{ $event->user?->name ?: 'System' }} · {{ $event->created_at->timezone('Asia/Dhaka')->format('d M Y, g:i a') }}</p>@if($event->note)<p class="mt-2 whitespace-pre-line text-sm text-slate-600">{{ $event->note }}</p>@endif</li>@empty<li class="text-sm admin-muted">New request received. Status changes will be recorded here.</li>@endforelse</ol></section>@endif
@endsection

