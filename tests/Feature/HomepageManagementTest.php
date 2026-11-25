<?php

use App\Models\HomeSlide;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
    Storage::fake('local');
});

/** @return array<string, mixed> */
function homeSlidePayload(array $overrides = []): array
{
    return [
        'title' => 'Reception',
        'kind' => 'image',
        'alt_text' => ['en' => 'Reception entrance', 'bn' => 'রিসেপশনের প্রবেশপথ'],
        'sort_order' => 10,
        'is_active' => true,
        ...$overrides,
    ];
}

test('only an administrator can manage homepage slides', function () {
    $this->get(route('admin.slides.index'))->assertRedirect(route('login'));
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('admin.slides.store'), homeSlidePayload())->assertForbidden();

    $this->assertDatabaseCount('home_slides', 0);
});

test('an administrator can publish and reorder image and video slides', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin);
    $video = new UploadedFile(public_path('images/practice-demo/reception-video.mp4'), 'reception.mp4', test: true);

    $this->get(route('admin.slides.create'))->assertOk()->assertSee('Upload media');
    $this->post(route('admin.slides.store'), homeSlidePayload(['media' => UploadedFile::fake()->image('reception.png')]))
        ->assertSessionHasNoErrors()->assertRedirect(route('admin.slides.index'));
    $this->post(route('admin.slides.store'), homeSlidePayload([
        'title' => 'Clinic tour', 'kind' => 'video', 'media' => $video, 'poster' => UploadedFile::fake()->image('cover.jpg'), 'sort_order' => 5,
    ]))->assertSessionHasNoErrors()->assertRedirect(route('admin.slides.index'));

    $imageSlide = HomeSlide::query()->where('kind', 'image')->sole();
    $videoSlide = HomeSlide::query()->where('kind', 'video')->sole();
    $videoUrl = route('home-slides.file', [$videoSlide, 'media']);
    $imageUrl = route('home-slides.file', [$imageSlide, 'media']);
    $posterUrl = route('home-slides.file', [$videoSlide, 'poster']);

    Storage::disk('local')->assertExists([$videoSlide->file_path, $videoSlide->poster_path, $imageSlide->file_path]);
    $this->get(route('admin.slides.index'))->assertOk()->assertSeeInOrder(['Clinic tour', 'Reception']);
    $this->get(route('admin.slides.edit', $videoSlide))->assertOk()->assertSee('Replace media');
    $this->get('/bn')->assertOk()->assertSeeInOrder([$videoUrl, $imageUrl])->assertSee($posterUrl);
    $this->get('/en')->assertOk()->assertSeeInOrder([$videoUrl, $imageUrl]);

    auth()->logout();
    $this->get($videoUrl, ['Range' => 'bytes=0-15'])
        ->assertStatus(206)
        ->assertHeader('Content-Type', 'video/mp4')
        ->assertHeader('Content-Length', '16');
    $this->get($posterUrl)->assertOk()->assertHeader('Content-Type', 'image/webp');

    $this->actingAs($admin)->put(route('admin.slides.update', $imageSlide), homeSlidePayload(['sort_order' => 1]))
        ->assertSessionHasNoErrors()->assertRedirect(route('admin.slides.index'));
    $this->get('/en')->assertSeeInOrder([$imageUrl, $videoUrl]);
});

test('hidden and removed slides have private files and replacing media removes the old file', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin);
    $this->post(route('admin.slides.store'), homeSlidePayload(['media' => UploadedFile::fake()->image('original.jpg')]))->assertRedirect();
    $slide = HomeSlide::query()->sole();
    $originalFile = $slide->file_path;

    $this->put(route('admin.slides.update', $slide), homeSlidePayload([
        'media' => UploadedFile::fake()->image('replacement.png'), 'is_active' => false,
    ]))->assertSessionHasNoErrors()->assertRedirect();
    Storage::disk('local')->assertMissing($originalFile);
    $slide->refresh();
    $url = route('home-slides.file', [$slide, 'media']);
    $this->get($url)->assertOk()->assertHeader('Content-Type', 'image/webp');

    auth()->logout();
    $this->get($url)->assertNotFound();
    $this->get('/bn')->assertDontSee('data-photo-slider', false);

    $this->actingAs($admin)->delete(route('admin.slides.destroy', $slide))->assertRedirect(route('admin.slides.index'));
    $this->assertModelMissing($slide);
    Storage::disk('local')->assertMissing($slide->file_path);
});

test('slide validation checks the actual media type and requires a replacement when the type changes', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $fakeVideo = UploadedFile::fake()->createWithContent('sample.mp4', 'plain text');

    $this->post(route('admin.slides.store'), homeSlidePayload(['kind' => 'video', 'media' => $fakeVideo]))->assertInvalid('media');
    $this->assertDatabaseCount('home_slides', 0);

    $slide = HomeSlide::factory()->create();
    $this->put(route('admin.slides.update', $slide), homeSlidePayload(['kind' => 'video']))->assertInvalid('media');
    expect($slide->fresh()->kind)->toBe('image');
});

test('settings update contact, social and homepage information in public views', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $settings = [
        'phone' => '+880 1700 123456',
        'email' => 'public@example.test',
        'notification_email' => 'private@example.test',
        'default_locale' => 'en',
        'instagram_url' => 'https://www.instagram.com/example-practice/',
        'linkedin_url' => 'https://www.linkedin.com/company/example-practice/',
        'tiktok_url' => 'https://www.tiktok.com/@example-practice',
        'site_name' => ['en' => 'Example Practice', 'bn' => 'নমুনা চেম্বার'],
        'home_notice' => ['en' => 'New visiting hours this week', 'bn' => 'এই সপ্তাহে নতুন সময়সূচি'],
        'footer_text' => ['en' => 'Practice footer', 'bn' => 'চেম্বারের তথ্য'],
        'address' => ['en' => 'Public clinic address', 'bn' => 'চেম্বারের ঠিকানা'],
    ];
    foreach (['tagline', 'seo_title', 'seo_description', 'privacy_text', 'appointment_notice', 'contact_notice'] as $field) {
        $settings[$field] = ['bn' => 'চেম্বারের তথ্য', 'en' => 'Practice information'];
    }

    $this->get(route('admin.settings.edit'))->assertOk()->assertSee('Social links')->assertSee('Homepage announcement');
    $this->put(route('admin.settings.update'), [...$settings, 'tiktok_url' => 'https://other.example.test/@example'])->assertInvalid('tiktok_url');
    $this->put(route('admin.settings.update'), $settings)->assertSessionHasNoErrors()->assertRedirect();

    $this->get('/en/contact')->assertOk()
        ->assertSee('tel:+8801700123456', false)
        ->assertSeeText('Public clinic address')
        ->assertSeeText('Practice footer')
        ->assertSee($settings['instagram_url'], false)
        ->assertSee($settings['linkedin_url'], false)
        ->assertSee($settings['tiktok_url'], false)
        ->assertDontSee('private@example.test');
    $this->get('/bn')->assertOk()->assertSeeText('এই সপ্তাহে নতুন সময়সূচি');
});
