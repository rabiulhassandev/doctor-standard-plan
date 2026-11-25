<?php

use App\Http\Controllers\HomeSlideFileController;
use App\Http\Controllers\PublicFormController;
use App\Http\Controllers\PublicImageController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Middleware\SetPublicLocale;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    $locale = $request->cookie('preferred_locale') ?? SiteSetting::current()->default_locale;

    return redirect()->route('public.home', ['locale' => in_array($locale, ['en', 'bn'], true) ? $locale : 'bn']);
});
require __DIR__.'/admin.php';
Route::get('/images/{path}', PublicImageController::class)->where('path', 'media/[A-Za-z0-9_.-]+')->name('images.show');
Route::get('/home-slides/{homeSlide}/{file}', HomeSlideFileController::class)
    ->whereNumber('homeSlide')->whereIn('file', ['media', 'poster'])->name('home-slides.file');
Route::get('/sitemap.xml', [PublicSiteController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', fn () => response("User-agent: *\nDisallow: /admin\nSitemap: ".route('sitemap')."\n", 200, ['Content-Type' => 'text/plain']))->name('robots');
Route::prefix('{locale}')->where(['locale' => 'en|bn'])->middleware(SetPublicLocale::class)->name('public.')->group(function (): void {
    Route::get('/', [PublicSiteController::class, 'home'])->name('home');
    Route::get('/home-page-v2', fn (string $locale): RedirectResponse => to_route('public.home', ['locale' => $locale], 301));
    foreach (['about', 'services', 'chambers', 'health', 'articles', 'videos', 'reels', 'gallery', 'privacy'] as $page) {
        Route::get('/'.$page, [PublicSiteController::class, $page])->name($page);
    }
    Route::get('/articles/{slug}', [PublicSiteController::class, 'article'])->name('article');
    Route::get('/media/{slug}', [PublicSiteController::class, 'media'])->name('media');
    Route::get('/appointment', [PublicSiteController::class, 'appointment'])->name('appointment');
    Route::post('/appointment', [PublicFormController::class, 'appointment'])->middleware('throttle:public-forms')->name('appointment.store');
    Route::get('/contact', [PublicSiteController::class, 'contact'])->name('contact');
    Route::post('/contact', [PublicFormController::class, 'inquiry'])->middleware('throttle:public-forms')->name('contact.store');
});
