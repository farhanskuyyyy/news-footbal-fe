<?php

namespace App\Http\Controllers;

use App\Services\FootballPortalService;
use App\Services\NewsService;
use App\Services\SeoService;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(SeoService $seo, NewsService $news, FootballPortalService $football): Response
    {
        $items = $news->all();
        $leagues = $football->getLeagues(true);
        if ($items === null || $leagues === null) {
            return response('Sitemap temporarily unavailable', 503)->header('Retry-After', '300');
        }

        $urls = array_map(fn ($route) => $seo->route($route), SeoService::PAGES);
        $pages = (int) ceil(count($items) / max(1, (int) config('news.per_page')));
        for ($page = 2; $page <= $pages; $page++) {
            $urls[] = $seo->route('news.index', ['page' => $page]);
        }
        foreach ($leagues as $league) {
            if (! empty($league['id']) && ctype_digit((string) $league['id'])) {
                $urls[] = $seo->route('football.index', ['league_id' => $league['id']]);
            }
        }
        foreach ($items as $item) {
            if (! empty($item['id']) && ctype_digit((string) $item['id'])) {
                $urls[] = $seo->route('news.show', ['id' => $item['id']]);
            }
        }
        // Never silently truncate discovery once the sitemap protocol limit is reached.
        $urls = array_values(array_unique($urls));
        abort_if(count($urls) > 50000, 503, 'Sitemap index required');

        return response()->view('seo.sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(SeoService $seo): Response
    {
        $body = "User-agent: *\nAllow: /\nDisallow: /api/\nDisallow: /home/standings\nDisallow: /up\n\nSitemap: ".$seo->url('sitemap.xml')."\n";

        return response($body)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
