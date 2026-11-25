<?php

namespace App\Http\Controllers;

use App\Models\HomeSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class HomeSlideFileController extends Controller
{
    public function __invoke(Request $request, HomeSlide $homeSlide, string $file): BinaryFileResponse
    {
        abort_unless($homeSlide->is_active || $request->user()?->is_admin, 404);

        $path = $file === 'poster' ? $homeSlide->poster_path : $homeSlide->file_path;
        abort_unless($path, 404);

        $type = match (pathinfo($path, PATHINFO_EXTENSION)) {
            'webp' => 'image/webp',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            default => abort(404),
        };
        $disk = Storage::disk('local');
        abort_unless($disk->exists($path), 404);

        return response()->file($disk->path($path), [
            'Content-Type' => $type,
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
