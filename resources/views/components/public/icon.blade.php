@props(['name' => 'cross'])
<svg {{ $attributes->merge(['class' => 'icon', 'width' => '24', 'height' => '24', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.65', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
    @switch($name)
        @case('home')<path d="m3 10 9-7 9 7v11H3zM9 21v-7h6v7"/>@break
        @case('cross')<path d="M9 3h6v6h6v6h-6v6H9v-6H3V9h6z"/>@break
        @case('phone')<path d="m7 3 3 5-2.5 2a16 16 0 0 0 6.5 6.5L16 14l5 3-1 4C10 22 2 14 3 4z"/>@break
        @case('calendar')<rect x="3" y="5" width="18" height="16" rx="3"/><path d="M7 3v4m10-4v4M3 11h18m-13 5h.01M12 16h.01M16 16h.01"/>@break
        @case('pin')<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>@break
        @case('clock')<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>@break
        @case('play')<path d="m9 5 11 7-11 7z"/>@break
        @case('pause')<path d="M9 5v14m6-14v14"/>@break
        @case('heart')<path d="M20.8 4.8a5.5 5.5 0 0 0-7.8 0l-1 1-1-1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.4a5.5 5.5 0 0 0 0-7.8Z"/><path d="M4 12h4l2-4 3 8 2-4h5"/>@break
        @case('book')<path d="M12 5c-3-2-7-2-10-1v16c3-1 7-1 10 1 3-2 7-2 10-1V4c-3-1-7-1-10 1Zm0 0v16"/>@break
        @case('shield')<path d="m12 2 9 4v6c0 5-9 10-9 10S3 17 3 12V6Z"/><path d="m8 12 3 3 5-6"/>@break
        @case('menu')<path d="M4 6h16M4 12h16M4 18h16"/>@break
        @case('close')<path d="m6 6 12 12M18 6 6 18"/>@break
        @case('chevron')<path d="m9 5 7 7-7 7"/>@break
        @case('mail')<rect x="2" y="4" width="20" height="16" rx="3"/><path d="m3 6 9 7 9-7"/>@break
        @case('chat')<path d="M21 11.5A9 9 0 0 1 7 19l-5 2 1.5-5A9 9 0 1 1 21 11.5Z"/><path d="M7 10h10M7 14h6"/>@break
        @case('check')<path d="m5 12 4 4L19 6"/>@break
        @case('globe')<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c5 5 5 13 0 18-5-5-5-13 0-18Z"/>@break
        @case('image')<rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8" cy="8" r="1.5"/><path d="m21 16-5-5-9 10"/>@break
        @case('stethoscope')<path d="M4 3v6a5 5 0 0 0 10 0V3M3 3h3m7 0h3M9 14v3a5 5 0 0 0 10 0v-3"/><circle cx="19" cy="11" r="3"/>@break
        @case('person')<circle cx="12" cy="7" r="4"/><path d="M3 22v-3a9 9 0 0 1 18 0v3zM8 15l4 5 4-5"/>@break
        @case('hospital')<path d="M7 22V5h10v17M2 22V11h5m10 0h5v11H2M9 22v-4h6v4m-3-15v6m-3-3h6"/>@break
        @case('reel')<rect x="4" y="3" width="16" height="18" rx="3"/><path d="M4 8h16m-9-5 3 5m-4 4 5 3-5 3z"/>@break
        @case('whatsapp')<path d="M21 11.6A9 9 0 0 1 7 19l-5 2 1.4-5A9 9 0 1 1 21 11.6Z"/><path d="m8 7 2 3-1 1a8 8 0 0 0 3.5 3.5l1-1 3 2-1 2A10 10 0 0 1 6 8z"/>@break
        @case('facebook')<path fill="currentColor" stroke="none" d="M14 22v-9h3l.5-4H14V7c0-1 .3-2 2-2h2V2.3C17.3 2.1 16 2 15 2c-3 0-5 1.8-5 5v2H7v4h3v9z"/>@break
        @case('youtube')<rect x="2" y="5" width="20" height="14" rx="4"/><path fill="currentColor" d="m10 9 5 3-5 3z"/>@break
        @case('instagram')<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17 7h.01"/>@break
        @case('linkedin')<path d="M5 10v9m0-14v.01M10 19v-9m0 3a4 4 0 0 1 8 0v6"/><circle cx="5" cy="5" r=".7"/>@break
        @case('tiktok')<path d="M14 4v11a4 4 0 1 1-4-4m4-7c1 3 3 4 6 4"/>@break
        @case('search')<circle cx="10" cy="10" r="6"/><path d="m14.5 14.5 5 5"/>@break
        @case('dashboard')<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>@break
        @case('settings')<path d="M4 7h16M4 17h16"/><circle cx="9" cy="7" r="2" fill="currentColor"/><circle cx="15" cy="17" r="2" fill="currentColor"/>@break
        @case('external')<path d="M13 5h6v6m0-6-8 8m-2-8H5v14h14v-4"/>@break
        @case('logout')<path d="M10 4H5v16h5m5-4 4-4-4-4m4 4H9"/>@break
        @case('tag')<path d="M20 13 13 20 4 11V4h7zM8 8h.01"/>@break
        @case('share')<circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="5" r="2.5"/><circle cx="18" cy="19" r="2.5"/><path d="m8.2 10.8 7.6-4.5m-7.6 6.9 7.6 4.5"/>@break
        @case('copy')<rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/>@break
        @default<circle cx="12" cy="12" r="9"/><path d="M12 8v4m0 4h.01"/>
    @endswitch
</svg>
