@extends('admin.layout')
@section('title', $definition['title'])
@section('actions')
    <a href="{{ route('admin.content.create', $section) }}" class="admin-button admin-button-primary">+ Add content</a>
@endsection

@section('content')
    <form method="GET" aria-label="Filter content" class="admin-surface mb-4 flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-0 flex-[1_1_14rem]">
            <label class="admin-field-label mb-1.5" for="content-search">Title</label>
            <input id="content-search" name="q" value="{{ $search }}" placeholder="Search titles" class="admin-control">
        </div>
        @if($definition['translated'])
            <div class="flex-[1_1_9rem] sm:max-w-48">
                <label class="admin-field-label mb-1.5" for="status">Visibility</label>
                <select id="status" name="status" class="admin-control">
                    <option value="">All statuses</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="published" @selected(request('status') === 'published')>Published</option>
                </select>
            </div>
        @endif
        <button class="admin-button" type="submit"><x-public.icon name="search" /> Search</button>
        @if(request()->anyFilled(['q', 'status']))
            <a href="{{ route('admin.content.index', $section) }}" class="admin-link self-center">Clear filters</a>
        @endif
    </form>

    <div class="admin-surface overflow-hidden">
        <table class="admin-table" role="table" aria-label="{{ $definition['title'] }}">
            <thead role="rowgroup">
                <tr role="row">
                    <th scope="col">Title</th>
                    <th scope="col">Visibility</th>
                    <th scope="col">Order</th>
                    <th scope="col">Updated</th>
                    <th scope="col"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody role="rowgroup">
                @forelse($records as $item)
                    @php
                        $displayTitle = $definition['translated']
                            ? ($item->translations->firstWhere('locale', 'en')?->title ?: $item->translations->firstWhere('locale', 'bn')?->title)
                            : $item->text($section === 'chambers' ? 'name' : 'title', 'en');
                        $status = $definition['translated'] ? $item->status : ($item->is_active ? 'active' : 'hidden');
                    @endphp
                    <tr role="row">
                        <td role="cell">
                            <p class="font-semibold">{{ $displayTitle ?: 'Untitled draft' }}</p>
                            @if($definition['translated'])
                                <div class="admin-muted mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs">
                                    @foreach(['bn', 'en'] as $language)
                                        <span>{{ strtoupper($language) }} · {{ $item->translations->firstWhere('locale', $language)?->status ?? 'missing' }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td role="cell" data-label="Visibility"><span class="admin-badge" data-status="{{ $status }}">{{ $status }}</span></td>
                        <td role="cell" data-label="Order" class="admin-muted">{{ $item->sort_order }}</td>
                        <td role="cell" data-label="Updated" class="admin-muted">{{ $item->updated_at->format('d M Y') }}</td>
                        <td role="cell" class="sm:text-right">
                            <a href="{{ route('admin.content.edit', [$section, $item->id]) }}" class="admin-button admin-button-small">Edit<span class="sr-only"> {{ $displayTitle }}</span></a>
                        </td>
                    </tr>
                @empty
                    <tr role="row">
                        <td colspan="5" role="cell" class="admin-table-empty">
                            <div class="admin-empty">
                                <span class="admin-icon-box"><x-public.icon name="book" /></span>
                                <p class="font-semibold">{{ request()->anyFilled(['q', 'status']) ? 'No matching content' : 'No content here yet' }}</p>
                                <p class="admin-muted text-sm">{{ request()->anyFilled(['q', 'status']) ? 'Adjust the search or clear the filters.' : 'Use Add content to create the first item.' }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($records->hasPages())<div class="mt-5">{{ $records->links() }}</div>@endif
@endsection
