<?php

namespace App\Http\Middleware;

use App\Services\SeoService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchIndexing
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $public = $request->routeIs(...[...SeoService::PAGES, 'news.show', 'football.team', 'football.player', 'football.fixture', 'seo.sitemap', 'seo.robots']);
        if (! $public || ! $response->isSuccessful() || $request->ajax()) {
            $response->headers->set('X-Robots-Tag', 'noindex, follow');
        }

        return $response;
    }
}
