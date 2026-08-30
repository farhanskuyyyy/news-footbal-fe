<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FootballPortalService
{
    protected string $baseUrl;

    protected int $timeout;

    protected int $cacheTtl;

    public function __construct()
    {
        $this->baseUrl = config('news.api_url', 'http://localhost:8082');
        $this->timeout = config('news.timeout', 5);
        $this->cacheTtl = config('news.cache_ttl', 60);
    }

    /**
     * Helper for GET requests with graceful error handling.
     */
    protected function get(string $endpoint, array $queryParams = [], int $cacheSeconds = 60): ?array
    {
        $cacheKey = 'football.'.md5($endpoint.serialize($queryParams));

        try {
            return Cache::remember($cacheKey, $cacheSeconds, function () use ($endpoint, $queryParams) {
                $response = Http::timeout($this->timeout)
                    ->acceptJson()
                    ->get("{$this->baseUrl}/portal/{$endpoint}", $queryParams);

                if ($response->notFound()) {
                    return null;
                }

                return $response->throw()->json();
            });
        } catch (ConnectionException|RequestException $e) {
            Log::warning("Football API error on /portal/{$endpoint}", ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function getLeagues(bool $activeOnly = false): ?array
    {
        $res = $this->get('leagues', ['active_only' => $activeOnly ? 'true' : 'false'], 120);

        return $res['data'] ?? [];
    }

    public function getLeagueSeasons(int $leagueId): ?array
    {
        $res = $this->get("leagues/{$leagueId}/seasons", [], 120);

        return $res ?? null;
    }

    public function getSeasonOverview(int $seasonId): ?array
    {
        $res = $this->get("seasons/{$seasonId}/overview", [], 60);

        return $res['data'] ?? null;
    }

    public function getSeasonStandings(int $seasonId): ?array
    {
        $res = $this->get("seasons/{$seasonId}/standings", [], 60);

        return $res['data'] ?? [];
    }

    public function getSeasonRounds(int $seasonId): ?array
    {
        $res = $this->get("seasons/{$seasonId}/rounds", [], 60);

        return $res['data'] ?? [];
    }

    public function getSeasonFixtures(int $seasonId, ?int $roundId = null): ?array
    {
        $params = [];
        if ($roundId) {
            $params['round_id'] = $roundId;
        }
        $res = $this->get("seasons/{$seasonId}/fixtures", $params, 30);

        return $res['data'] ?? [];
    }

    public function getSeasonTeams(int $seasonId): ?array
    {
        $res = $this->get("seasons/{$seasonId}/teams", [], 120);

        return $res['data'] ?? [];
    }

    public function getSeasonTopscorers(int $seasonId, ?int $typeId = null): ?array
    {
        $params = [];
        if ($typeId) {
            $params['type_id'] = $typeId;
        }

        return $this->get("seasons/{$seasonId}/topscorers", $params, 60);
    }

    public function getSeasonTransfers(int $seasonId): ?array
    {
        $res = $this->get("seasons/{$seasonId}/transfers", [], 60);

        return $res['data'] ?? [];
    }

    public function getFixtureDetail(int $fixtureId): ?array
    {
        $res = $this->get("fixtures/{$fixtureId}", [], 30);

        return $res['data'] ?? null;
    }

    public function getTeamDetail(int $teamId, ?int $seasonId = null): ?array
    {
        $params = [];
        if ($seasonId) {
            $params['season_id'] = $seasonId;
        }
        $res = $this->get("teams/{$teamId}", $params, 120);

        return $res['data'] ?? null;
    }

    public function getPlayerDetail(int $playerId): ?array
    {
        $res = $this->get("players/{$playerId}", [], 120);

        return $res['data'] ?? null;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Live Sportmonks proxy (backend /sportmonks/* passthrough).
    // Used for real-time data that is NOT persisted in the portal DB:
    // livescores, head-to-head, predictions, fixtures-by-date, search.
    // ─────────────────────────────────────────────────────────────────────

    /**
     * GET the backend Sportmonks proxy. Returns the raw decoded JSON (Sportmonks
     * envelope, i.e. ['data' => ...]) or null on failure.
     */
    protected function getProxy(string $path, array $queryParams = [], int $cacheSeconds = 15): ?array
    {
        $cacheKey = 'sm.'.md5($path.serialize($queryParams));

        try {
            return Cache::remember($cacheKey, $cacheSeconds, function () use ($path, $queryParams) {
                $response = Http::timeout($this->timeout)
                    ->acceptJson()
                    ->get("{$this->baseUrl}/sportmonks/{$path}", $queryParams);

                if ($response->notFound()) {
                    return null;
                }

                return $response->throw()->json();
            });
        } catch (ConnectionException|RequestException $e) {
            Log::warning("Sportmonks proxy error on /sportmonks/{$path}", ['error' => $e->getMessage()]);

            return null;
        }
    }

    /** Currently in-play matches with score/event/participant context. */
    public function getLiveInplay(): array
    {
        $res = $this->getProxy('livescores/inplay', [
            'include' => 'participants;scores;state;league;events.type',
        ], 15);

        return $res['data'] ?? [];
    }

    /** Head-to-head history between two teams. */
    public function getHeadToHead(int $teamA, int $teamB): array
    {
        $res = $this->getProxy("fixtures/head-to-head/{$teamA}/{$teamB}", [
            'include' => 'participants;scores;league;state',
        ], 3600);

        return $res['data'] ?? [];
    }

    /** Win/draw/loss & market probabilities for a fixture. */
    public function getFixturePredictions(int $fixtureId): array
    {
        $res = $this->getProxy("predictions/probabilities/fixtures/{$fixtureId}", [
            'include' => 'type',
        ], 600);

        return $res['data'] ?? [];
    }

    /** All fixtures kicking off on a given Y-m-d date. */
    public function getFixturesByDate(string $date): array
    {
        $res = $this->getProxy("fixtures/date/{$date}", [
            'include' => 'participants;scores;state;league',
        ], 60);

        return $res['data'] ?? [];
    }

    /**
     * Upcoming fixtures for a team, fetched LIVE from the Sportmonks proxy
     * (not persisted). Returns up to $limit not-yet-finished fixtures sorted by
     * kickoff. Raw Sportmonks fixture shape (participants/state/league) — render
     * with the football.partials.live-card partial.
     */
    public function getTeamUpcoming(int $teamId, int $limit = 5): array
    {
        $start = date('Y-m-d');
        $end = date('Y-m-d', strtotime('+150 days'));

        $res = $this->getProxy("fixtures/between/{$start}/{$end}/{$teamId}", [
            'include' => 'participants;league;state',
        ], 600);
        $data = $res['data'] ?? [];

        // Drop already-finished fixtures, sort by kickoff ascending
        $finished = ['FT', 'AET', 'FT_PEN'];
        $data = array_values(array_filter($data, function ($f) use ($finished) {
            $code = $f['state']['short_name'] ?? $f['state']['state'] ?? '';

            return ! in_array($code, $finished, true);
        }));
        usort($data, fn ($a, $b) => strcmp($a['starting_at'] ?? '', $b['starting_at'] ?? ''));

        return array_slice($data, 0, $limit);
    }

    /** Pre-match odds for a fixture (grouped by market/bookmaker downstream). */
    public function getFixtureOdds(int $fixtureId): array
    {
        $res = $this->getProxy("odds/pre-match/fixtures/{$fixtureId}", [
            'include' => 'market;bookmaker',
        ], 600);

        return $res['data'] ?? [];
    }

    /**
     * On-demand: ask the backend to scrape a single fixture (with all match
     * data) and save it. Returns true on success. Busts the cached fixture
     * detail so the next read reflects the freshly scraped data.
     */
    public function scrapeFixture(int $fixtureId): bool
    {
        try {
            $response = Http::timeout(90)
                ->acceptJson()
                ->post("{$this->baseUrl}/sportmonks/scrape/fixture/{$fixtureId}");

            if ($response->successful()) {
                Cache::forget('football.'.md5("fixtures/{$fixtureId}".serialize([])));

                return true;
            }
        } catch (ConnectionException|RequestException $e) {
            Log::warning("On-demand fixture scrape failed for {$fixtureId}", ['error' => $e->getMessage()]);
        }

        return false;
    }

    /** Search teams / players / leagues by name via the proxy. */
    public function search(string $type, string $name): array
    {
        $type = in_array($type, ['teams', 'players', 'leagues'], true) ? $type : 'teams';
        $res = $this->getProxy("{$type}/search/".rawurlencode($name), [], 120);

        return $res['data'] ?? [];
    }
}
