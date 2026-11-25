@extends('admin.layout')
@section('title', 'Dashboard')
@section('heading')Welcome, {{ auth()->user()->name }}@endsection
@section('intro')<p class="admin-muted mt-1.5 text-sm">A quick look at your appointments, messages, and website.</p>@endsection

@section('content')
    @if($profile->is_demo || $settings->is_demo)
        <div class="mb-5 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950">
            <x-public.icon name="info" />
            <p><strong>Demo content is active.</strong> Review your <a class="underline" href="{{ route('admin.profile.edit') }}">doctor profile</a> and <a class="underline" href="{{ route('admin.settings.edit') }}">site settings</a> before launch.</p>
        </div>
    @endif

    <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
        @foreach([
            ['New appointment requests', $newAppointments, 'calendar', route('admin.inbox.index', ['kind' => 'appointments', 'status' => 'new'])],
            ['New inquiries', $newInquiries, 'mail', route('admin.inbox.index', ['kind' => 'inquiries', 'status' => 'new'])],
            ['Active chambers', $activeChambers, 'hospital', route('admin.content.index', 'chambers')],
            ['Published articles', $articleCount, 'book', route('admin.content.index', ['section' => 'articles', 'status' => 'published'])],
        ] as [$label, $count, $icon, $href])
            <a href="{{ $href }}" class="admin-surface admin-stat flex min-w-0 flex-col items-start gap-2.5 p-4 sm:p-5">
                <span class="admin-icon-box"><x-public.icon :name="$icon" /></span>
                <span class="admin-muted text-sm font-semibold leading-snug">{{ $label }}</span>
                <strong class="mt-auto text-3xl font-bold tracking-tight tabular-nums">{{ $count }}</strong>
            </a>
        @endforeach
    </div>

    <div class="mt-6 grid items-start gap-5 xl:grid-cols-[minmax(0,1.65fr)_minmax(0,1fr)]">
        <section class="admin-surface overflow-hidden" aria-labelledby="recent-appointments">
            <div class="admin-panel-header">
                <h2 id="recent-appointments">Recent appointment requests</h2>
                <a href="{{ route('admin.inbox.index', 'appointments') }}" class="admin-button admin-button-small">View inbox <x-public.icon name="chevron" /></a>
            </div>
            @forelse($recentRequests as $item)
                <a href="{{ route('admin.inbox.show', ['appointments', $item->id]) }}" class="admin-list-link">
                    <span class="min-w-0">
                        <span class="block font-semibold break-words">{{ $item->name }}</span>
                        <span class="admin-muted mt-1 block text-xs">{{ $item->chamber?->text('name', 'en') ?: 'Chamber to be arranged' }} · {{ $item->created_at->timezone('Asia/Dhaka')->format('d M, g:i a') }}</span>
                    </span>
                    <span class="admin-badge" data-status="{{ $item->status }}">{{ str_replace('_', ' ', $item->status) }}</span>
                    <x-public.icon name="chevron" class="admin-muted" />
                </a>
            @empty
                <div class="admin-empty m-4 sm:m-5">
                    <span class="admin-icon-box"><x-public.icon name="calendar" /></span>
                    <p class="font-semibold">No appointment requests yet</p>
                    <p class="admin-muted text-sm">New requests from the website will appear here.</p>
                </div>
            @endforelse
        </section>

        <section class="admin-surface overflow-hidden" aria-labelledby="quick-actions">
            <div class="admin-panel-header"><h2 id="quick-actions">Quick actions</h2></div>
            @foreach([
                ['Edit doctor profile', 'Update the photo and biography', 'person', route('admin.profile.edit')],
                ['Manage visiting hours', 'Edit chamber schedules and notices', 'clock', route('admin.content.index', 'chambers')],
                ['Manage homepage slider', 'Arrange homepage images and videos', 'image', route('admin.slides.index')],
                ['Update site settings', 'Edit contact details and social links', 'settings', route('admin.settings.edit')],
            ] as [$label, $detail, $icon, $href])
                <a href="{{ $href }}" class="admin-list-link">
                    <span class="admin-icon-box"><x-public.icon :name="$icon" /></span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold">{{ $label }}</span>
                        <span class="admin-muted mt-0.5 block text-xs">{{ $detail }}</span>
                    </span>
                    <x-public.icon name="chevron" class="admin-muted" />
                </a>
            @endforeach
        </section>
    </div>
@endsection
