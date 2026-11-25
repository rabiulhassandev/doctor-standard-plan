<?php

use App\Http\Controllers\Admin\ContentDefinition;
use App\Jobs\SendInboxNotification;
use App\Models\AppointmentRequest;
use App\Models\Article;
use App\Models\Chamber;
use App\Models\DoctorProfile;
use App\Models\Inquiry;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
});

function practiceOwner(): User
{
    $owner = User::factory()->create();
    $owner->forceFill(['is_admin' => true])->save();

    return $owner;
}

/** @return array<string, mixed> */
function serviceEditorPayload(): array
{
    return [
        'slug' => 'general-consultation',
        'status' => 'published',
        'sort_order' => 0,
        'featured' => true,
        'translations' => [
            'en' => ['status' => 'published', 'title' => 'General consultation', 'description' => 'Professional advice', 'body' => 'Arrange a visit to discuss your needs.'],
            'bn' => ['status' => 'draft', 'title' => null],
        ],
    ];
}

it('redirects guests and refuses non-owner accounts across the admin boundary', function (): void {
    $this->get('/admin')->assertRedirect(route('login'));
    $ordinaryUser = User::factory()->create();

    $this->actingAs($ordinaryUser)->get('/admin')->assertForbidden();
    $this->actingAs($ordinaryUser)->post('/admin/content/services', serviceEditorPayload())->assertForbidden();
    $this->assertDatabaseCount('services', 0);
});

it('signs in only provisioned owners and signs them out', function (): void {
    $owner = practiceOwner();
    $owner->password = 'OwnerPassword123!';
    $owner->save();

    $this->post('/admin/login', ['email' => $owner->email, 'password' => 'OwnerPassword123!'])->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticatedAs($owner);
    $this->post('/admin/logout')->assertRedirect(route('login'));
    $this->assertGuest();
});

it('seeds a demo admin with working credentials and leaves existing accounts intact', function (): void {
    $this->seed();
    $this->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'password'])->assertRedirect(route('admin.dashboard'));

    $demoAdmin = User::query()->where('email', 'admin@example.com')->sole();
    $this->assertAuthenticatedAs($demoAdmin);
    $demoAdmin->update(['password' => 'ReplacementPassword123!']);

    $this->seed();

    expect(Hash::check('ReplacementPassword123!', $demoAdmin->fresh()->password))->toBeTrue();
    $this->assertDatabaseCount('users', 1);
});

it('skips the demo admin in production', function (): void {
    $this->app->instance('env', 'production');

    $this->artisan('db:seed', ['--force' => true])->assertSuccessful();

    $this->assertDatabaseMissing('users', ['email' => 'admin@example.com']);
});

it('refuses otherwise valid non-owner credentials', function (): void {
    $user = User::factory()->create(['password' => 'OrdinaryPassword123!']);

    $this->post('/admin/login', ['email' => $user->email, 'password' => 'OrdinaryPassword123!'])->assertInvalid('email');
    $this->assertGuest();
});

it('renders every custom admin list and create editor', function (string $section): void {
    $this->actingAs(practiceOwner());

    $this->get(route('admin.content.index', $section))->assertOk()->assertHeader('Cache-Control', 'max-age=0, no-store, private');
    $this->get(route('admin.content.create', $section))->assertOk()->assertSee('Save changes');
})->with(array_keys(ContentDefinition::all()));

it('renders editable existing records including date and category controls', function (string $section): void {
    $definition = ContentDefinition::get($section);
    $record = $definition['model']::factory()->create();
    $this->actingAs(practiceOwner());

    $this->get(route('admin.content.edit', [$section, $record->id]))->assertOk()->assertSee('Archive content');
})->with(array_keys(ContentDefinition::all()));

it('creates updates and archives bilingual content while retaining an incomplete edition', function (): void {
    $this->actingAs(practiceOwner());
    $data = serviceEditorPayload();

    $this->post(route('admin.content.store', 'services'), $data)->assertSessionHasNoErrors()->assertRedirect();
    $service = Service::firstOrFail();
    $this->assertDatabaseHas('content_translations', ['translatable_id' => $service->id, 'locale' => 'bn', 'title' => '', 'status' => 'draft']);

    $data['translations']['en']['title'] = 'Updated consultation';
    $this->put(route('admin.content.update', ['services', $service->id]), $data)->assertSessionHasNoErrors()->assertRedirect();
    expect($service->fresh()->text('title', 'en'))->toBe('Updated consultation');
    $this->delete(route('admin.content.destroy', ['services', $service->id]), ['confirm' => 1])->assertRedirect();
    expect($service->fresh()->status)->toBe('draft');
    $this->assertModelExists($service);
});

it('rejects incomplete publication and unapproved testimonial publication', function (): void {
    $this->actingAs(practiceOwner());
    $data = serviceEditorPayload();
    $data['translations']['en']['title'] = '';

    $this->post(route('admin.content.store', 'services'), $data)->assertInvalid('translations.en.title');
    $this->assertDatabaseCount('services', 0);

    $data = serviceEditorPayload();
    $data['patient_name'] = 'Patient A';
    $this->post(route('admin.content.store', 'testimonials'), $data)->assertInvalid('consent_at');
    $this->assertDatabaseCount('testimonials', 0);
});

it('rejects active upload formats and ignores attempted stored path injection', function (): void {
    Storage::fake('local');
    $this->actingAs(practiceOwner());
    $data = serviceEditorPayload();
    $data['image'] = UploadedFile::fake()->createWithContent('active.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');

    $this->post(route('admin.content.store', 'services'), $data)->assertInvalid('image');
    $this->assertDatabaseCount('services', 0);

    unset($data['image']);
    $data['image_path'] = '../../.env';
    $this->post(route('admin.content.store', 'services'), $data)->assertSessionHasNoErrors()->assertRedirect();
    expect(Service::firstOrFail()->image_path)->toBeNull();
});

it('reencodes uploaded images and keeps drafts in private storage', function (): void {
    Storage::fake('local');
    $this->actingAs(practiceOwner());
    $data = serviceEditorPayload();
    $data['status'] = 'draft';
    $data['image'] = UploadedFile::fake()->image('photo.jpg', 1800, 1200);

    $this->post(route('admin.content.store', 'services'), $data)->assertSessionHasNoErrors()->assertRedirect();
    $service = Service::firstOrFail();
    Storage::disk('local')->assertExists($service->image_path);
    expect($service->image_path)->toStartWith('media/')->toEndWith('.webp');
    $dimensions = getimagesize(Storage::disk('local')->path($service->image_path));
    expect($dimensions[0])->toBe(1600);
});

it('validates chamber hours and preserves chamber references when archived', function (): void {
    $chamber = Chamber::factory()->create();
    $appointment = AppointmentRequest::factory()->create(['chamber_id' => $chamber->id]);
    $this->actingAs(practiceOwner());
    $data = [
        'slug' => $chamber->slug, 'sort_order' => 0, 'is_active' => true,
        'name' => ['bn' => 'চেম্বার', 'en' => 'Chamber'],
        'hospital_name' => ['bn' => '', 'en' => ''],
        'address' => ['bn' => 'ঢাকা', 'en' => 'Dhaka'],
        'notice' => ['bn' => '', 'en' => ''],
        'windows' => [
            ['weekday' => 1, 'starts_at' => '09:00', 'ends_at' => '11:00'],
            ['weekday' => 1, 'starts_at' => '10:00', 'ends_at' => '12:00'],
        ],
    ];

    $this->put(route('admin.content.update', ['chambers', $chamber->id]), $data)->assertInvalid('windows.1.starts_at');
    $data['windows'][1]['starts_at'] = '11:00';
    $this->put(route('admin.content.update', ['chambers', $chamber->id]), $data)->assertSessionHasNoErrors()->assertRedirect();
    expect($chamber->visitingWindows()->count())->toBe(2);

    $this->delete(route('admin.content.destroy', ['chambers', $chamber->id]), ['confirm' => true])->assertRedirect();
    expect($chamber->fresh()->is_active)->toBeFalse();
    expect($appointment->fresh()->chamber_id)->toBe($chamber->id);
});

it('normalizes shorts links and refuses arbitrary embed providers', function (): void {
    $this->actingAs(practiceOwner());
    $data = serviceEditorPayload();
    $data['kind'] = 'reel';
    $data['video_url'] = 'https://www.youtube.com/shorts/ABCDEFGHIJK';
    $data['duration_seconds'] = 45;

    $this->post(route('admin.content.store', 'media'), $data)->assertSessionHasNoErrors()->assertRedirect();
    $this->assertDatabaseHas('media_items', ['provider' => 'youtube', 'provider_id' => 'ABCDEFGHIJK', 'kind' => 'reel']);

    $data['slug'] = 'dangerous-video';
    $data['video_url'] = 'https://youtube.com.evil.example/watch?v=ABCDEFGHIJK';
    $this->post(route('admin.content.store', 'media'), $data)->assertInvalid('video_url');
    $this->assertDatabaseCount('media_items', 1);
});

it('updates appointment statuses with an audit trail and rejects invalid or stale transitions', function (): void {
    $owner = practiceOwner();
    $appointment = AppointmentRequest::factory()->create();
    $this->actingAs($owner);

    $this->put(route('admin.inbox.update', ['appointments', $appointment->id]), ['status' => 'contacted', 'expected_status' => 'new', 'notes' => 'Called to discuss date.'])->assertSessionHasNoErrors()->assertRedirect();
    $this->assertDatabaseHas('appointment_status_events', ['appointment_request_id' => $appointment->id, 'user_id' => $owner->id, 'from_status' => 'new', 'to_status' => 'contacted']);
    $this->put(route('admin.inbox.update', ['appointments', $appointment->id]), ['status' => 'completed', 'expected_status' => 'contacted'])->assertInvalid('status');
    $this->put(route('admin.inbox.update', ['appointments', $appointment->id]), ['status' => 'confirmed', 'expected_status' => 'new'])->assertInvalid('status');
    expect($appointment->fresh()->status)->toBe('contacted');
});

it('renders private inquiry and appointment details with escaped content', function (): void {
    $this->actingAs(practiceOwner());
    $appointment = AppointmentRequest::factory()->create();
    $inquiry = Inquiry::factory()->create(['message' => '<script>alert("unsafe")</script>']);

    $this->get(route('admin.inbox.show', ['appointments', $appointment->id]))->assertOk()->assertSee('Status history');
    $this->get(route('admin.inbox.show', ['inquiries', $inquiry->id]))->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert("unsafe")</script>', false);
});

it('retries failed notifications without sending from an already sent record', function (): void {
    Queue::fake([SendInboxNotification::class]);
    $this->actingAs(practiceOwner());
    $appointment = AppointmentRequest::factory()->create(['notification_status' => 'failed']);

    $this->post(route('admin.inbox.retry', ['appointments', $appointment->id]))->assertRedirect();
    Queue::assertPushed(SendInboxNotification::class, fn ($job): bool => $job->kind === 'appointments' && $job->recordId === $appointment->id);
    expect($appointment->fresh()->notification_status)->toBe('pending');
});

it('restricts draft previews to the owner and includes draft content safely', function (): void {
    $article = Article::factory()->create(['status' => 'draft']);
    $article->translations()->create(['locale' => 'en', 'status' => 'draft', 'title' => 'Private draft', 'body' => '<script>draft</script>']);

    $this->get(route('admin.content.preview', ['articles', $article->id, 'en']))->assertRedirect(route('login'));
    $this->actingAs(practiceOwner())->get(route('admin.content.preview', ['articles', $article->id, 'en']))->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow')->assertSee('Private draft')->assertSee('&lt;script&gt;draft&lt;/script&gt;', false);
});

it('sends recovery only to owner accounts and resets with the framework broker', function (): void {
    Notification::fake();
    $owner = practiceOwner();

    $this->post(route('password.email'), ['email' => $owner->email])->assertRedirect();
    Notification::assertSentTo($owner, ResetPassword::class);
    $token = Password::broker()->createToken($owner);
    $this->post(route('password.update'), ['email' => $owner->email, 'token' => $token, 'password' => 'NewOwnerPassword123', 'password_confirmation' => 'NewOwnerPassword123'])->assertRedirect(route('login'));
    expect(Hash::check('NewOwnerPassword123', $owner->fresh()->password))->toBeTrue();
});

it('renders the profile settings and dashboard and prevents identity privilege injection', function (): void {
    $owner = practiceOwner();
    $this->actingAs($owner);

    $this->get(route('admin.dashboard'))->assertOk();
    $this->get(route('admin.profile.edit'))->assertOk();
    $this->get(route('admin.settings.edit'))->assertOk();
    $profile = DoctorProfile::factory()->create();
    $this->put(route('admin.profile.update'), [
        'name' => ['bn' => 'ডাক্তার', 'en' => 'Doctor'],
        'designation' => ['bn' => '', 'en' => 'Consultant'],
        'specialty' => ['bn' => '', 'en' => 'Medicine'],
        'qualifications' => ['bn' => '', 'en' => 'MBBS'],
        'introduction' => ['bn' => '', 'en' => 'Welcome'],
        'biography' => ['bn' => '', 'en' => 'Practice biography'],
        'is_admin' => false, 'image_path' => '../../.env',
    ])->assertSessionHasNoErrors()->assertRedirect();
    expect($profile->fresh()->name['en'])->toBe('Doctor');
    expect($owner->fresh()->is_admin)->toBeTrue();
});

it('validates analytics and social settings as typed identifiers and links', function (): void {
    $this->actingAs(practiceOwner());
    $data = [
        'notification_email' => 'clinic@example.test', 'default_locale' => 'bn', 'analytics_id' => '<script>bad</script>',
    ];
    foreach (['site_name', 'tagline', 'footer_text', 'seo_title', 'seo_description', 'address', 'privacy_text', 'appointment_notice', 'contact_notice'] as $field) {
        $data[$field] = ['bn' => 'তথ্য', 'en' => 'Practice information'];
    }

    $this->put(route('admin.settings.update'), $data)->assertInvalid('analytics_id');
    $this->assertDatabaseCount('site_settings', 0);
    $data['analytics_id'] = 'G-ABC1234567';
    $this->put(route('admin.settings.update'), $data)->assertSessionHasNoErrors()->assertRedirect();
    expect(SiteSetting::current()->notification_email)->toBe('clinic@example.test');
});
