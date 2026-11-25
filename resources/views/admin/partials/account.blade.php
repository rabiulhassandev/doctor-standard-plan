<div class="admin-account">
    <div class="min-w-0">
        <p class="break-words text-sm font-semibold">{{ $owner->name }}</p>
        <p class="mt-0.5 text-xs break-all">{{ $owner->email }}</p>
    </div>
    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit" class="admin-nav-link mt-3 w-full">
            <x-public.icon name="logout" /> Sign out
        </button>
    </form>
</div>
