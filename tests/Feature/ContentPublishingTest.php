<?php

use App\Models\Article;
use App\Models\ContentTranslation;
use App\Models\DoctorProfile;
use App\Models\MediaItem;
use App\Models\Service;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

test('public content requires a published record and a published requested translation', function () {
    $this->freezeTime();
    $visible = Article::factory()->published()->create();
    $untranslated = Article::factory()->published()->create();
    $untranslated->translations()->where('locale', 'bn')->update(['status' => 'draft']);
    Article::factory()->published()->create(['published_at' => now()->addDay()]);
    $draft = Article::factory()->create();
    ContentTranslation::factory()->for($draft, 'translatable')->create(['locale' => 'bn', 'status' => 'published']);

    $articles = Article::publishedIn('bn')->get();

    expect($articles->modelKeys())->toBe([$visible->id]);
    expect($articles->first()->relationLoaded('translations'))->toBeTrue();
});

test('content text never exposes a draft translation or silently switches the article language', function () {
    $article = Article::factory()->published()->create();
    $article->translations()->where('locale', 'en')->update(['title' => 'Published article']);
    $article->translations()->where('locale', 'bn')->update(['title' => 'Private draft', 'status' => 'draft']);

    expect($article->text('title', 'en'))->toBe('Published article');
    expect($article->text('title', 'bn'))->toBe('');
});

test('removing content removes its translations', function () {
    $service = Service::factory()->published()->create();

    $service->delete();

    $this->assertModelMissing($service);
    $this->assertDatabaseCount('content_translations', 0);
});

test('shared profile information falls back to its available language', function () {
    $profile = DoctorProfile::factory()->make(['name' => ['en' => 'Example physician']]);

    expect($profile->text('name', 'bn'))->toBe('Example physician');
    expect($profile->text('registration_number', 'bn'))->toBe('');
});

test('demo seeding refuses production without adding content', function () {
    app()->detectEnvironment(fn (): string => 'production');

    expect(fn () => (new DemoSeeder)->run())->toThrow(RuntimeException::class, 'Demo content may only be seeded outside production.');

    $this->assertDatabaseCount('doctor_profiles', 0);
    $this->assertDatabaseCount('services', 0);
});

test('demo seeding protects a configured practice identity', function () {
    $profile = DoctorProfile::factory()->create(['is_demo' => false, 'name' => ['en' => 'Configured physician']]);

    expect(fn () => $this->seed(DemoSeeder::class))->toThrow(RuntimeException::class, 'Demo seeding cannot replace an existing practice identity.');

    expect($profile->fresh()->text('name', 'en'))->toBe('Configured physician');
    $this->assertDatabaseCount('doctor_profiles', 1);
    $this->assertDatabaseCount('services', 0);
});

test('demo seeding is repeatable and preserves edited demo content', function () {
    Storage::fake('local');
    $this->seed(DemoSeeder::class);
    $profile = DoctorProfile::current();
    $profile->update(['name' => ['en' => 'Edited demo name']]);

    $this->seed(DemoSeeder::class);

    expect($profile->fresh()->text('name', 'en'))->toBe('Edited demo name');
    $this->assertDatabaseCount('doctor_profiles', 1);
    $this->assertDatabaseCount('services', 6);
    $this->assertDatabaseCount('articles', 5);
    $this->assertDatabaseCount('gallery_items', 1);
    $this->assertDatabaseCount('home_slides', 2);
    $this->assertDatabaseCount('visiting_windows', 3);
    $this->assertDatabaseCount('testimonials', 0);
    $this->assertDatabaseCount('users', 0);
});

test('the default seeder publishes categorized videos and reels in both languages', function () {
    $this->withoutVite();
    Storage::fake('local');
    $this->seed();

    $this->get('/bn/videos?category=health-discussions')
        ->assertOk()
        ->assertSeeText('উচ্চ রক্তচাপ সম্পর্কে জানুন')
        ->assertDontSeeText('মীনার সঙ্গে হাত ধোয়া শিখি');

    $this->get('/en/reels?category=everyday-health')
        ->assertOk()
        ->assertSeeText('How light affects sleep')
        ->assertSeeText('Hydration and mental wellbeing')
        ->assertSeeText('Flexibility and stretching')
        ->assertDontSeeText('Learn about handwashing with Meena');

    $this->get('/bn/media/demo-light-and-sleep')
        ->assertOk()
        ->assertSeeText('ঘুমের ওপর আলোর প্রভাব')
        ->assertSee('data-video-id="7jJjhhfcnWI"', false);

    $this->get('/en/media/demo-understanding-high-blood-pressure')
        ->assertOk()
        ->assertSeeText('Understanding high blood pressure');
});

test('seeding media again preserves editorial changes without duplicating entries', function () {
    Storage::fake('local');
    $this->seed();
    $media = MediaItem::query()->where('slug', 'demo-light-and-sleep')->firstOrFail();
    $media->translations()->where('locale', 'en')->update(['title' => 'Edited title']);

    $this->seed();

    expect(MediaItem::publishedIn('en')->where('kind', 'video')->count())->toBe(7);
    expect(MediaItem::publishedIn('bn')->where('kind', 'reel')->count())->toBe(6);
    expect(MediaItem::query()->count())->toBe(14);
    expect($media->fresh()->text('title', 'en'))->toBe('Edited title');
});
