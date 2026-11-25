<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title', 'Dashboard') · Practice Studio</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-theme">
    <a href="#admin-main" class="skip-link">Skip to content</a>
    @php($owner = auth()->user())

    <div class="min-h-dvh lg:grid lg:grid-cols-[240px_minmax(0,1fr)]">
        <aside class="admin-rail sticky top-0 hidden h-dvh flex-col lg:flex" aria-label="Practice Studio">
            <a href="{{ route('admin.dashboard') }}" class="admin-brand">
                <span class="admin-brand-mark"><x-public.icon name="cross" /></span>
                Practice Studio
            </a>
            <div class="min-h-0 flex-1 overflow-y-auto px-3 pb-4">
                @include('admin.partials.navigation')
            </div>
            @include('admin.partials.account', ['owner' => $owner])
        </aside>

        <div class="min-w-0">
            <details class="admin-rail admin-mobile-menu lg:hidden">
                <summary class="admin-brand">
                    <span class="admin-brand-mark"><x-public.icon name="cross" /></span>
                    <span>Practice Studio</span>
                    <span class="admin-menu-label"><x-public.icon name="menu" /> Menu</span>
                </summary>
                <div class="max-h-[65dvh] overflow-y-auto px-3 pb-3">
                    @include('admin.partials.navigation')
                </div>
                @include('admin.partials.account', ['owner' => $owner])
            </details>

            <main id="admin-main" tabindex="-1" class="mx-auto w-full max-w-[1440px] px-4 py-6 sm:px-7 sm:py-9 xl:px-10">
                <header class="mb-6 flex flex-wrap items-center justify-between gap-x-6 gap-y-4">
                    <div class="min-w-0">
                        <h1 class="admin-page-title">
                            @hasSection('heading')
                                @yield('heading')
                            @else
                                @yield('title', 'Dashboard')
                            @endif
                        </h1>
                        @yield('intro')
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @yield('actions')
                        <a href="{{ url('/') }}" target="_blank" rel="noopener" class="admin-button">
                            <x-public.icon name="external" /> View website<span class="sr-only"> (opens in a new tab)</span>
                        </a>
                    </div>
                </header>

                @if(session('status'))
                    <div role="status" class="mb-6 rounded-xl border border-teal-200 bg-teal-50 px-5 py-4 text-sm text-teal-900">{{ session('status') }}</div>
                @endif
                @if($errors->any())
                    <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-900">
                        <p class="font-semibold">Please correct the following</p>
                        <ul class="mt-2 list-inside list-disc space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
