@extends('admin.layout')

@section('title', 'Settings')
@section('intro')<p class="admin-muted mt-1.5 text-sm">Manage contact details, social profiles, branding, and website copy.</p>@endsection

@section('content')
    <nav aria-label="Settings sections" class="admin-surface mb-5 flex flex-wrap gap-2 p-3">
        @foreach(['contact-details' => 'Contact details', 'social-links' => 'Social links', 'language-analytics' => 'Language & analytics', 'site-imagery' => 'Site imagery', 'site-copy' => 'Bilingual copy'] as $anchor => $label)
            <a class="admin-button admin-button-small" href="#{{ $anchor }}">{{ $label }}</a>
        @endforeach
    </nav>

    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.settings.update') }}" class="space-y-5">
        @csrf @method('PUT')

        <section id="contact-details" class="admin-surface p-4 sm:p-6">
            <h2 class="mb-5 text-lg">Contact details</h2>
            <div class="grid gap-5 sm:grid-cols-2">
                @foreach([
                    'phone' => ['Public phone', 'tel', ''],
                    'whatsapp' => ['WhatsApp number', 'tel', 'Use international format, for example +8801XXXXXXXXX.'],
                    'email' => ['Public contact email', 'email', ''],
                    'notification_email' => ['Private notification recipient', 'email', 'Appointment and inquiry alerts are sent here.'],
                    'map_url' => ['Primary Google Maps link', 'url', ''],
                ] as $field => [$label, $type, $help])
                    @include('admin.partials.field', ['name' => $field, 'label' => $label, 'type' => $type, 'value' => $settings->{$field}, 'help' => $help])
                @endforeach
            </div>
        </section>

        <section id="social-links" class="admin-surface p-4 sm:p-6">
            <h2 class="mb-5 text-lg">Social links</h2>
            <div class="grid gap-5 sm:grid-cols-2">
                @foreach(['facebook_url' => 'Facebook page', 'youtube_url' => 'YouTube channel', 'instagram_url' => 'Instagram profile', 'linkedin_url' => 'LinkedIn profile or page', 'tiktok_url' => 'TikTok profile'] as $field => $label)
                    @include('admin.partials.field', ['name' => $field, 'label' => $label, 'type' => 'url', 'value' => $settings->{$field}])
                @endforeach
            </div>
        </section>

        <section id="language-analytics" class="admin-surface p-4 sm:p-6">
            <h2 class="mb-5 text-lg">Language & analytics</h2>
            <div class="grid gap-5 sm:grid-cols-2">
                @include('admin.partials.field', ['name' => 'default_locale', 'label' => 'Default public language', 'type' => 'select', 'choices' => ['bn' => 'বাংলা', 'en' => 'English'], 'value' => $settings->default_locale ?? 'bn', 'help' => 'Existing links keep their explicit language.'])
                @include('admin.partials.field', ['name' => 'analytics_id', 'label' => 'Google Analytics 4 measurement ID', 'value' => $settings->analytics_id, 'help' => 'Use a G- measurement ID. Leave blank to disable analytics.'])
            </div>
        </section>

        <section id="site-imagery" class="admin-surface p-4 sm:p-6">
            <h2 class="mb-5 text-lg">Site imagery</h2>
            <div class="grid gap-5 sm:grid-cols-2">
                @foreach(['logo' => 'Site logo', 'og_image' => 'Default social sharing image'] as $field => $label)
                    <div class="min-w-0">
                        @include('admin.partials.field', ['name' => $field, 'label' => $label, 'type' => 'file', 'help' => 'JPEG, PNG or WebP, up to 5 MB and 4000 × 4000 pixels.'])
                        @if($settings->{$field.'_path'})
                            <img src="{{ route('images.show', ['path' => $settings->{$field.'_path'}]) }}" alt="Current {{ strtolower($label) }}" class="mt-4 max-h-28 max-w-full rounded-lg object-contain">
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        <div id="site-copy" class="grid items-start gap-5 xl:grid-cols-2">
            @foreach(['bn' => 'বাংলা', 'en' => 'English'] as $locale => $localeLabel)
                <section lang="{{ $locale }}" class="admin-surface p-4 sm:p-6">
                    <h2 class="mb-5 text-lg">{{ $localeLabel }} site copy</h2>
                    <div class="space-y-5">
                        @foreach([
                            'site_name' => 'Site name', 'tagline' => 'Tagline', 'home_notice' => 'Homepage announcement',
                            'footer_text' => 'Footer text', 'seo_title' => 'Default SEO title', 'seo_description' => 'Default SEO description',
                            'address' => 'Contact address', 'privacy_text' => 'Privacy notice', 'appointment_notice' => 'Appointment request instructions',
                            'contact_notice' => 'Contact form instructions',
                        ] as $field => $label)
                            @include('admin.partials.field', [
                                'name' => $field.'['.$locale.']',
                                'label' => $label,
                                'type' => in_array($field, ['site_name', 'tagline', 'seo_title']) ? 'text' : 'textarea',
                                'value' => $settings->{$field}[$locale] ?? '',
                                'help' => $field === 'privacy_text' ? 'Describe collection, purpose, contact, retention and deletion choices for this practice.' : '',
                                'rows' => $field === 'privacy_text' ? 10 : 4,
                            ])
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <button class="admin-button admin-button-primary" type="submit">Save site settings</button>
    </form>
@endsection
