<?php

use App\Models\Article;
use App\Models\Chamber;
use App\Models\DoctorProfile;
use App\Models\Service;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);
beforeEach(function () {
    $this->withoutVite();
});

test('the header links the configured public hotline and falls back to chambers without a number', function () {
    $this->get('/en')->assertOk()->assertSee('class="reference-hotline" href="'.route('public.chambers', ['locale' => 'en']).'"', false);

    SiteSetting::factory()->create(['id' => 1, 'phone' => '+880 (2) 1234-5678']);

    $this->get('/en')->assertOk()->assertSee('class="reference-hotline" href="tel:+880212345678"', false);
});

test('featured content takes priority in the limited homepage selection', function () {
    Service::factory()->published()->count(3)->create();
    $featured = Service::factory()->published()->create(['featured' => true, 'sort_order' => 50]);
    $featured->translations()->where('locale', 'en')->update(['title' => 'Featured care']);

    $this->get('/en')->assertOk()->assertSeeInOrder(['Featured care', 'Example Service']);
});

test('the main homepage shows the carousel and published services', function () {
    $service = Service::factory()->published()->create();
    DoctorProfile::factory()->create(['id' => 1, 'is_demo' => false, 'image_path' => 'media/portrait.webp']);

    $this->get('/en')
        ->assertOk()
        ->assertSee($service->text('title', 'en'))
        ->assertSee('reference-home"', false)
        ->assertSee('data-photo-interval="3000"', false)
        ->assertSeeInOrder(['/images/media/portrait.webp', '/images/practice-demo/clinic-reception.jpg'])
        ->assertSee('<link rel="canonical" href="'.route('public.home', ['locale' => 'en']).'">', false)
        ->assertDontSee('reference-home-intro', false)
        ->assertDontSee('name="robots" content="noindex, follow"', false);
});

test('old homepage preview links permanently redirect to the matching language', function () {
    $this->get('/en/home-page-v2')->assertStatus(301)->assertRedirect('/en');
    $this->get('/bn/home-page-v2')->assertStatus(301)->assertRedirect('/bn');
    $this->get('/sitemap.xml')->assertOk()->assertSee(route('public.home', ['locale' => 'bn']))->assertDontSee('home-page-v2');
});
test('root follows explicit language preference', function () {
    $this->get('/')->assertRedirect('/bn');
    $this->withCookie('preferred_locale', 'en')->get('/')->assertRedirect('/en');
    $this->get('/fr')->assertNotFound();
});

test('a configured default language is used for first visits', function () {
    SiteSetting::factory()->create(['id' => 1, 'default_locale' => 'en']);

    $this->get('/')->assertRedirect('/en');
    $this->withCookie('preferred_locale', 'bn')->get('/')->assertRedirect('/bn');
});
test('public page renders in both languages', function (string $locale, string $page) {
    $this->get('/'.$locale.$page)->assertOk()->assertSee('lang="'.$locale.'"', false)->assertSee('reference-dark-theme', false);
})->with(['en', 'bn'])->with(['', '/about', '/services', '/chambers', '/health', '/articles', '/videos', '/reels', '/gallery', '/contact', '/appointment', '/privacy']);
test('only published translations have public detail pages and sitemap entries', function () {
    $article = Article::factory()->published()->create();
    $article->translations()->where('locale', 'bn')->update(['status' => 'draft']);
    $draft = Article::factory()->create();
    $this->get('/en/articles/'.$article->slug)->assertOk();
    $this->get('/bn/articles/'.$article->slug)->assertRedirect('/en/articles/'.$article->slug);
    $this->get('/en/articles/'.$draft->slug)->assertNotFound();
    $this->get('/sitemap.xml')->assertOk()->assertSee('/en/articles/'.$article->slug, false)->assertDontSee('/bn/articles/'.$article->slug, false)->assertDontSee($draft->slug);
});
test('article text is escaped and missing alternates are not advertised', function () {
    $article = Article::factory()->published()->create();
    $article->translations()->where('locale', 'en')->update(['body' => '<script>alert(1)</script>', 'title' => 'A safe title']);
    $article->translations()->where('locale', 'bn')->update(['status' => 'draft']);
    $this->get('/en/articles/'.$article->slug)->assertOk()->assertDontSee('<script>alert(1)</script>', false)->assertDontSee('hreflang="bn" href="http://localhost/bn/articles/'.$article->slug.'"', false);
});
test('forms avoid analytics and are private noncacheable pages', function () {
    SiteSetting::factory()->create(['id' => 1, 'analytics_id' => 'G-TEST123']);
    $this->get('/en')->assertOk()->assertSee('data-analytics-id="G-TEST123"', false)->assertDontSee('googletagmanager.com', false);
    $this->get('/en/appointment')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow')->assertDontSee('data-analytics-id=', false);
    $this->get('/en/contact')->assertOk()->assertSee('name="submission_token"', false);
});
test('inactive chambers are omitted and published articles can be searched', function () {
    $chamber = Chamber::factory()->create(['is_active' => false]);
    $article = Article::factory()->published()->create();
    $article->translations()->where('locale', 'en')->update(['title' => 'Unique clinic guide']);
    $this->get('/en/chambers')->assertDontSee($chamber->text('name', 'en'));
    $this->get('/en/articles?q=Unique')->assertSee('Unique clinic guide');
    $this->get('/en/articles?q=Absent')->assertDontSee('Unique clinic guide');
});
