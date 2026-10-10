<?php

namespace Tests\Feature;

use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SeoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Http::preventStrayRequests();
        config(['seo.url' => 'https://pitchticker.kreasifarhan.id']);
    }

    public function test_landing_has_production_canonical_and_valid_schema(): void
    {
        Http::fake(['*' => Http::response(['data' => []])]);
        $response = $this->get('/?utm_source=test&intro=1')->assertOk()
            ->assertSee('<link rel="canonical" href="https://pitchticker.kreasifarhan.id/">', false)
            ->assertSee('Football Live Scores, News &amp; Standings', false)
            ->assertSee('name="description"', false)
            ->assertSee('property="og:url"', false);
        $graph = $this->schema($response->getContent());
        $this->assertSame('WebSite', $graph['@graph'][1]['@type']);
        $this->assertSame('https://pitchticker.kreasifarhan.id/', $graph['@graph'][0]['url']);
    }

    public function test_distinct_league_and_news_pages_keep_content_parameters(): void
    {
        Http::fake(['*' => Http::response(['data' => []])]);
        $this->get('/football?utm_source=test&league_id=8&season_id=9&tab=fixtures')
            ->assertOk()->assertSee('href="https://pitchticker.kreasifarhan.id/football?league_id=8&amp;season_id=9&amp;tab=fixtures"', false);
        $this->get('/berita?page=2&utm_campaign=test')
            ->assertOk()->assertSee('href="https://pitchticker.kreasifarhan.id/berita?page=2"', false);
        $this->get('/berita?page=1')->assertOk()
            ->assertSee('href="https://pitchticker.kreasifarhan.id/berita"', false);
    }

    public function test_sourced_news_preserves_publisher_and_escapes_schema(): void
    {
        $item = [
            'id' => 12, 'title' => 'Football </script><script>alert(1)</script>',
            'description' => 'A sourced football summary.', 'content' => '',
            'source' => 'Original publisher', 'author' => 'Actual author',
            'url' => 'https://example.com/original-report',
            'url_to_image' => 'https://example.com/photo.jpg',
            'published_at' => '2026-10-10T10:00:00Z',
        ];
        Http::fake(['*' => Http::response(['data' => $item])]);
        $response = $this->get('/berita/12')->assertOk();
        $article = $this->schema($response->getContent())['@graph'][0]['about'];
        $this->assertSame('NewsArticle', $article['@type']);
        $this->assertSame('Original publisher', $article['publisher']['name']);
        $this->assertSame($item['url'], $article['url']);
        $this->assertSame($item['title'], $article['headline']);
        $response->assertDontSee('</script><script>alert(1)</script>', false);
    }

    public function test_utility_and_loading_pages_are_not_indexable(): void
    {
        Http::fake(['*' => Http::response(['data' => null])]);
        foreach (['/login', '/upload', '/football/search?q=arsenal', '/football/compare', '/admin', '/home/standings'] as $url) {
            $this->get($url)->assertHeader('X-Robots-Tag', 'noindex, follow');
        }
        $this->get('/football/fixtures/999')->assertOk()->assertSee('content="noindex, follow"', false);
    }

    public function test_sitemap_contains_public_routes_and_actual_entities_only(): void
    {
        Http::fake([
            '*/news' => Http::response(['data' => [['id' => 12], ['id' => 12]]]),
            '*' => Http::response(['data' => [['id' => 8, 'name' => 'Premier League']]]),
        ]);
        $response = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);
        $urls = array_map(fn ($entry) => (string) $entry->loc, iterator_to_array($xml->url, false));
        $this->assertContains('https://pitchticker.kreasifarhan.id/', $urls);
        $this->assertContains('https://pitchticker.kreasifarhan.id/berita/12', $urls);
        $this->assertContains('https://pitchticker.kreasifarhan.id/football?league_id=8', $urls);
        $this->assertCount(count(array_unique($urls)), $urls);
        $this->assertNotContains('https://pitchticker.kreasifarhan.id/login', $urls);
    }

    public function test_sitemap_failure_is_temporary_and_robots_announces_sitemap(): void
    {
        Http::fake(['*' => Http::response([], 503)]);
        $this->get('/sitemap.xml')->assertStatus(503)->assertHeader('Retry-After', '300');
        $this->get('/robots.txt')->assertOk()
            ->assertSee('Sitemap: https://pitchticker.kreasifarhan.id/sitemap.xml', false);
    }

    public function test_localized_metadata_and_optional_search_console_token(): void
    {
        Http::fake(['*' => Http::response(['data' => []])]);
        config(['seo.google_verification' => 'test-verification-token']);
        $this->withSession(['locale' => 'id'])->get('/')->assertOk()
            ->assertSee('Skor Langsung, Berita &amp; Klasemen Sepak Bola', false)
            ->assertSee('name="google-site-verification" content="test-verification-token"', false)
            ->assertDontSee('hreflang=', false);
    }

    public function test_football_entities_only_use_available_data(): void
    {
        $service = app(SeoService::class);
        $request = Request::create('/football/fixtures/12');
        $route = (new Route('GET', 'football/fixtures/{id}', fn () => null))->name('football.fixture');
        $request->setRouteResolver(fn () => $route);
        $this->app->instance('request', $request);
        $data = [
            'fixture' => ['name' => 'Home vs Away', 'starting_at' => '2026-10-10 18:00:00'],
            'home_team' => ['name' => 'Home'], 'away_team' => ['name' => 'Away'],
            'venue' => ['name' => 'Actual stadium'],
        ];
        $entity = $service->metadata('football.fixture', $data, 'Home vs Away')['schema']['@graph'][0]['mainEntity'];
        $this->assertSame('SportsEvent', $entity['@type']);
        $this->assertSame('2026-10-10T18:00:00+00:00', $entity['startDate']);
        $this->assertSame('Actual stadium', $entity['location']['name']);
        $this->assertArrayNotHasKey('offers', $entity);
        $data['fixture']['starting_at'] = 'not a date';
        $this->assertArrayNotHasKey('mainEntity', $service->metadata('football.fixture', $data, 'Match')['schema']['@graph'][0]);
        $this->assertFalse($service->metadata('football.fixture_loading', $data, 'Match')['indexable']);
    }

    private function schema(string $html): array
    {
        preg_match('~<script type="application/ld\+json">(.*?)</script>~s', $html, $matches);

        return json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
    }
}
