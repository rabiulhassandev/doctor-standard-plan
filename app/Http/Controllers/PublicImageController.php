<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\DoctorProfile;
use App\Models\GalleryItem;
use App\Models\MediaItem;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicImageController extends Controller
{
    public function __invoke(Request $request, string $path): BinaryFileResponse
    {
        abort_unless(preg_match('~^media/[a-zA-Z0-9_-]+\\.webp$~', $path), 404);
        $allowed = (bool) $request->user()?->is_admin;
        if (! $allowed) {
            $allowed = DoctorProfile::where('image_path', $path)->exists()
                || SiteSetting::where('logo_path', $path)->orWhere('og_image_path', $path)->exists();
            foreach ([Article::class, MediaItem::class, Service::class, GalleryItem::class, Testimonial::class] as $model) {
                if ($allowed) {
                    break;
                }
                foreach (['en', 'bn'] as $locale) {
                    $query = $model::publishedIn($locale)->where('image_path', $path);
                    if ($model === Testimonial::class) {
                        $query->whereNotNull('consent_at');
                    }
                    if ($query->exists()) {
                        $allowed = true;
                        break;
                    }
                }
            }
        }
        abort_unless($allowed && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), [
            'Content-Type' => 'image/webp', 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store',
        ]);
    }
}
