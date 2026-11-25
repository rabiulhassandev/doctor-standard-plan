@php
    $groups = [
        '' => [
            ['Dashboard', 'dashboard', 'admin.dashboard', 'admin.dashboard', []],
            ['Settings', 'settings', 'admin.settings.edit', 'admin.settings.*', []],
        ],
        'Inbox' => [
            ['Appointment requests', 'calendar', 'admin.inbox.index', 'admin.inbox.*', ['kind' => 'appointments']],
            ['Contact inquiries', 'mail', 'admin.inbox.index', 'admin.inbox.*', ['kind' => 'inquiries']],
        ],
        'Practice' => [
            ['Doctor profile', 'person', 'admin.profile.edit', 'admin.profile.*', []],
            ['Chambers & hours', 'hospital', 'admin.content.index', 'admin.content.*', ['section' => 'chambers']],
            ['Services', 'stethoscope', 'admin.content.index', 'admin.content.*', ['section' => 'services']],
            ['Qualifications', 'shield', 'admin.content.index', 'admin.content.*', ['section' => 'credentials']],
            ['Testimonials', 'chat', 'admin.content.index', 'admin.content.*', ['section' => 'testimonials']],
        ],
        'Website' => [
            ['Homepage slider', 'image', 'admin.slides.index', 'admin.slides.*', []],
            ['Articles', 'book', 'admin.content.index', 'admin.content.*', ['section' => 'articles']],
            ['Videos & reels', 'play', 'admin.content.index', 'admin.content.*', ['section' => 'media']],
            ['Gallery', 'image', 'admin.content.index', 'admin.content.*', ['section' => 'gallery']],
            ['Categories', 'tag', 'admin.content.index', 'admin.content.*', ['section' => 'categories']],
        ],
    ];
@endphp

<nav aria-label="Administration">
    @foreach($groups as $group => $links)
        @if($group !== '')<p class="admin-nav-title">{{ $group }}</p>@endif
        <ul class="grid gap-1">
            @foreach($links as [$label, $icon, $route, $pattern, $parameters])
                @php
                    $current = request()->routeIs($pattern) && collect($parameters)->every(
                        fn ($value, $key) => request()->route($key) === $value
                    );
                @endphp
                <li>
                    <a href="{{ route($route, $parameters) }}" class="admin-nav-link" @if($current) aria-current="page" @endif>
                        <x-public.icon :name="$icon" /> <span>{{ $label }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endforeach
</nav>
