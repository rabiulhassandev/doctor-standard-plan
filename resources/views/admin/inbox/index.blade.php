@extends('admin.layout')
@section('title', $kind === 'appointments' ? 'Appointment requests' : 'Contact inquiries')
@section('intro')<p class="admin-muted mt-1.5 text-sm">{{ $kind === 'appointments' ? 'Contact patients to arrange and confirm their visit.' : 'Review questions sent through the contact form.' }}</p>@endsection

@section('content')
    <form method="GET" aria-label="Filter inbox" class="admin-surface mb-4 flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-0 flex-[1_1_13rem]">
            <label class="admin-field-label mb-1.5" for="inbox-search">Search</label>
            <input id="inbox-search" name="q" value="{{ $search }}" placeholder="Name, reference or contact" class="admin-control">
        </div>
        <div class="min-w-0 flex-[1_1_8rem] sm:max-w-40">
            <label class="admin-field-label mb-1.5" for="status">Status</label>
            <select id="status" name="status" class="admin-control">
                <option value="">All statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>
        @if($chambers->isNotEmpty())
            <div class="min-w-0 flex-[1_1_10rem] sm:max-w-52">
                <label class="admin-field-label mb-1.5" for="inbox-chamber">Chamber</label>
                <select id="inbox-chamber" name="chamber" class="admin-control">
                    <option value="">All chambers</option>
                    @foreach($chambers as $chamber)
                        <option value="{{ $chamber->id }}" @selected(request()->integer('chamber') === $chamber->id)>{{ $chamber->text('name', 'en') }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="min-w-0 flex-[1_1_9rem] sm:max-w-44">
            <label class="admin-field-label mb-1.5" for="inbox-date">Received date</label>
            <input id="inbox-date" name="received_on" type="date" value="{{ request()->string('received_on') }}" class="admin-control">
        </div>
        <button class="admin-button" type="submit"><x-public.icon name="search" /> Filter</button>
        @if(request()->anyFilled(['q', 'status', 'chamber', 'received_on']))
            <a href="{{ route('admin.inbox.index', $kind) }}" class="admin-link self-center">Clear filters</a>
        @endif
    </form>

    <div class="admin-surface overflow-hidden">
        <table class="admin-table" role="table" aria-label="{{ $kind === 'appointments' ? 'Appointment requests' : 'Contact inquiries' }}">
            <thead role="rowgroup">
                <tr role="row">
                    <th scope="col">Name & contact</th>
                    <th scope="col">Status</th>
                    <th scope="col">Received</th>
                    <th scope="col">Alert email</th>
                    <th scope="col"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody role="rowgroup">
                @forelse($records as $item)
                    <tr role="row">
                        <td role="cell">
                            <p class="font-semibold">{{ $item->name }}</p>
                            <p class="admin-muted mt-0.5 text-xs">{{ $kind === 'appointments' ? $item->phone : $item->reply_to }}</p>
                        </td>
                        <td role="cell" data-label="Status"><span class="admin-badge" data-status="{{ $item->status }}">{{ str_replace('_', ' ', $item->status) }}</span></td>
                        <td role="cell" data-label="Received" class="admin-muted">{{ $item->created_at->timezone('Asia/Dhaka')->format('d M Y, g:i a') }}</td>
                        <td role="cell" data-label="Alert email"><span class="admin-badge" data-status="{{ $item->notification_status }}">{{ $item->notification_status }}</span></td>
                        <td role="cell" class="sm:text-right"><a href="{{ route('admin.inbox.show', [$kind, $item->id]) }}" class="admin-button admin-button-small">View<span class="sr-only"> {{ $item->name }}</span></a></td>
                    </tr>
                @empty
                    <tr role="row">
                        <td colspan="5" role="cell" class="admin-table-empty">
                            <div class="admin-empty">
                                <span class="admin-icon-box"><x-public.icon :name="$kind === 'appointments' ? 'calendar' : 'mail'" /></span>
                                <p class="font-semibold">No records in this view</p>
                                <p class="admin-muted text-sm">{{ request()->anyFilled(['q', 'status', 'chamber', 'received_on']) ? 'Adjust or clear the filters to see more records.' : 'New submissions from the website will appear here.' }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($records->hasPages())<div class="mt-5">{{ $records->links() }}</div>@endif
@endsection
