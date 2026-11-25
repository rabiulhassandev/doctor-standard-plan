<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Chamber;
use App\Models\Credential;
use App\Models\DoctorProfile;
use App\Models\GalleryItem;
use App\Models\HomeSlide;
use App\Models\MediaItem;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Services\PublicSeo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class PublicSiteController extends Controller
{
    public function home(Request $request): View
    {
        $locale = app()->getLocale();

        return $this->page($request, 'home', [
            'managedSlides' => HomeSlide::query()->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
            'services' => Service::publishedIn($locale)->orderByDesc('featured')->ordered()->limit(3)->get(),
            'articles' => Article::publishedIn($locale)->orderByDesc('featured')->orderByDesc('published_at')->limit(3)->get(),
            'media' => MediaItem::publishedIn($locale)->where('kind', 'video')->orderByDesc('featured')->ordered()->limit(3)->get(),
            'reels' => MediaItem::publishedIn($locale)->where('kind', 'reel')->orderByDesc('featured')->ordered()->limit(5)->get(),
            'videoCategories' => Category::where('is_active', true)->where('kind', 'media')->orderBy('sort_order')->orderBy('id')->limit(3)->get(),
            'testimonials' => Testimonial::publishedIn($locale)->whereNotNull('consent_at')->orderByDesc('featured')->ordered()->limit(3)->get(),
            'gallery' => GalleryItem::publishedIn($locale)->whereNotNull('image_path')->orderByDesc('featured')->ordered()->limit(4)->get(),
        ]);
    }

    public function about(Request $request): View
    {
        return $this->page($request, 'about', ['credentials' => Credential::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(), 'testimonials' => Testimonial::publishedIn(app()->getLocale())->whereNotNull('consent_at')->ordered()->get()]);
    }

    public function services(Request $request): View
    {
        return $this->page($request, 'services', ['services' => Service::publishedIn(app()->getLocale())->ordered()->get()]);
    }

    public function chambers(Request $request): View
    {
        return $this->page($request, 'chambers');
    }

    public function health(Request $request): View
    {
        $locale = app()->getLocale();

        return $this->page($request, 'health', [
            'articles' => Article::publishedIn($locale)->orderByDesc('published_at')->limit(3)->get(),
            'media' => MediaItem::publishedIn($locale)->where('kind', 'video')->ordered()->limit(3)->get(),
            'reels' => MediaItem::publishedIn($locale)->where('kind', 'reel')->ordered()->limit(4)->get(),
        ]);
    }

    public function articles(Request $request): View
    {
        return $this->listing($request, 'articles', Article::class);
    }

    public function videos(Request $request): View
    {
        return $this->listing($request, 'videos', MediaItem::class, 'video');
    }

    public function reels(Request $request): View
    {
        return $this->listing($request, 'reels', MediaItem::class, 'reel');
    }

    private function listing(Request $request, string $page, string $model, ?string $kind = null): View
    {
        $query = $model::publishedIn(app()->getLocale())->with('category')->when($kind, fn ($q) => $q->where('kind', $kind));
        $categories = Category::where('is_active', true)->where('kind', $model === Article::class ? 'article' : 'media')->orderBy('sort_order')->orderBy('id')->get();
        if ($request->filled('category')) {
            $category = $categories->firstWhere('slug', $request->query('category'));
            abort_unless($category, 404);
            $query->where('category_id', $category->id);
        }
        if ($request->filled('q')) {
            $term = mb_substr($request->string('q')->toString(), 0, 100);
            $query->whereHas('translations', fn ($translation) => $translation->where('locale', app()->getLocale())->where('status', 'published')->where('title', 'like', '%'.$term.'%'));
        }

        $data = ['items' => $query->orderByDesc('featured')->orderBy('sort_order')->orderByDesc('id')->paginate(12)->withQueryString(), 'categories' => $categories];
        if ($kind === 'video' && ! $request->anyFilled(['category', 'q'])) {
            $data['relatedReels'] = MediaItem::publishedIn(app()->getLocale())->where('kind', 'reel')->orderByDesc('featured')->ordered()->limit(6)->get();
        }

        return $this->page($request, $page, $data);
    }

    public function article(Request $request, string $locale, string $slug): View|RedirectResponse
    {
        return $this->detail($request, Article::class, $slug, 'article');
    }

    public function media(Request $request, string $locale, string $slug): View|RedirectResponse
    {
        return $this->detail($request, MediaItem::class, $slug, 'media');
    }

    private function detail(Request $request, string $model, string $slug, string $page): View|RedirectResponse
    {
        $item = $model::publishedIn(app()->getLocale())->where('slug', $slug)->first();
        if (! $item) {
            $other = app()->getLocale() === 'en' ? 'bn' : 'en';
            $available = $model::publishedIn($other)->where('slug', $slug)->firstOrFail();

            return to_route('public.'.$page, ['locale' => $other, 'slug' => $available->slug])->with('status', $other === 'bn' ? 'এই লেখাটি বর্তমানে বাংলায় পাওয়া যাচ্ছে।' : 'This content is currently available in English.');
        }

        return $this->page($request, $page, ['item' => $item]);
    }

    public function gallery(Request $request): View
    {
        $query = GalleryItem::publishedIn(app()->getLocale());
        $categories = GalleryItem::publishedIn(app()->getLocale())->whereNotNull('category')->distinct()->pluck('category');
        if ($request->filled('category')) {
            $query->where('category', $request->string('category')->toString());
        }

        return $this->page($request, 'gallery', ['items' => $query->ordered()->paginate(12)->withQueryString(), 'categories' => $categories]);
    }

    public function appointment(Request $request): View
    {
        return $this->form($request, 'appointment', 'appointment_tokens');
    }

    public function contact(Request $request): View
    {
        return $this->form($request, 'contact', 'inquiry_tokens');
    }

    private function form(Request $request, string $page, string $key): View
    {
        $token = (string) Str::uuid();
        $request->session()->put($key, array_slice([...$request->session()->get($key, []), $token], -10));

        return $this->page($request, $page, ['submissionToken' => $token]);
    }

    public function privacy(Request $request): View
    {
        return $this->page($request, 'privacy');
    }

    private function page(Request $request, string $page, array $data = []): View
    {
        $profile = DoctorProfile::current();
        $settings = SiteSetting::current();
        $locale = app()->getLocale();
        $chambers = Chamber::where('is_active', true)->with(['visitingWindows' => fn ($query) => $query->where('is_active', true)])->orderBy('sort_order')->get();
        $seo = app(PublicSeo::class)->forPage($request, $page, $profile, $settings, $data['item'] ?? null);

        return view('public.'.$page, [...compact('profile', 'settings', 'chambers', 'seo', 'locale'), ...$data]);
    }

    public function sitemap(): Response
    {
        $urls = [];
        foreach (['bn', 'en'] as $locale) {
            foreach (['home', 'about', 'services', 'chambers', 'health', 'articles', 'videos', 'reels', 'gallery'] as $page) {
                $urls[] = route('public.'.$page, ['locale' => $locale]);
            }
            foreach ([Article::class => 'article', MediaItem::class => 'media'] as $model => $page) {
                foreach ($model::publishedIn($locale)->cursor() as $item) {
                    $urls[] = route('public.'.$page, ['locale' => $locale, 'slug' => $item->slug]);
                }
            }
        }
        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $url) {
            $xml .= '<url><loc>'.htmlspecialchars($url, ENT_XML1).'</loc></url>';
        }

        return response($xml.'</urlset>', 200, ['Content-Type' => 'application/xml']);
    }
}
