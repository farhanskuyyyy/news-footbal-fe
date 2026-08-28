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
}
