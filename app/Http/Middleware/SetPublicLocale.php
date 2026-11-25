<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetPublicLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');
        abort_unless(in_array($locale, ['en', 'bn'], true), 404);
        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);
        $response = $next($request);
        $response->headers->set('Content-Language', $locale);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->setCookie(cookie('preferred_locale', $locale, 60 * 24 * 365));
        if ($request->routeIs('public.appointment*', 'public.contact*')) {
            $response->headers->set('Cache-Control', 'no-store, private');
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }
}
