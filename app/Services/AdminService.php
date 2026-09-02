<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin proxy to the Go backend's Sportmonks scraper admin endpoints
 * (/sportmonks/scrape/*, /scrape/jobs, /sync/status).
 */
class AdminService
{
    protected string $baseUrl;

    protected int $timeout;

    /** Allowed bulk scrape jobs → backend endpoint path. */
    public const JOBS = [
        'core' => 'scrape/core',
        'leagues' => 'scrape/leagues',
        'football' => 'scrape/football',
        'fixture-details' => 'scrape/fixture-details',
        'player-statistics' => 'scrape/player-statistics',
    ];

    public function __construct()
    {
        $this->baseUrl = config('news.api_url', 'http://localhost:8082');
        $this->timeout = 15;
    }

    /** Trigger a bulk scrape job (async on the backend). */
    public function trigger(string $job, bool $force = false): array
    {
        if (! isset(self::JOBS[$job])) {
            return ['ok' => false, 'message' => 'Job tidak dikenal.'];
        }

        $params = $force ? ['force' => 'true'] : [];

        return $this->post(self::JOBS[$job], $params);
    }

    /** Trigger the football scrape, optionally scoped to a league and/or season. */
    public function triggerFootball(bool $force, ?int $leagueId, ?int $seasonId): array
    {
        $params = [];
        if ($force) {
            $params['force'] = 'true';
        }
        if ($leagueId) {
            $params['league_id'] = $leagueId;
        }
        if ($seasonId) {
            $params['season_id'] = $seasonId;
        }

        return $this->post('scrape/football', $params);
    }

    /** Enable/disable a league for scraping (the backend `status` flag). */
    public function setLeagueStatus(int $leagueId, bool $status): bool
    {
        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->post("{$this->baseUrl}/portal/leagues/{$leagueId}/status", ['status' => $status]);

            return $response->successful();
        } catch (ConnectionException|RequestException $e) {
            Log::warning("Set league status failed for {$leagueId}", ['error' => $e->getMessage()]);

            return false;
        }
    }

    /** Scrape a single fixture by id. */
    public function scrapeFixture(int $fixtureId): array
    {
        return $this->post("scrape/fixture/{$fixtureId}", []);
    }

    /** Stop a running background job. */
    public function stop(string $job): array
    {
        return $this->post("scrape/stop/{$job}", []);
    }

    /** Names of currently running background jobs. */
    public function runningJobs(): array
    {
        $res = $this->get('scrape/jobs');

        return $res['running'] ?? [];
    }

    /** Per-table sync status. */
    public function syncStatus(): array
    {
        $res = $this->get('sync/status');

        // Backend may return {data:[...]} or a bare array/object.
        return $res['data'] ?? $res ?? [];
    }

    protected function post(string $path, array $params): array
    {
        try {
            // Backend scrape handlers read these via query params (c.QueryParam),
            // so send them in the query string, not the JSON body.
            $url = "{$this->baseUrl}/sportmonks/{$path}";
            if (! empty($params)) {
                $url .= '?'.http_build_query($params);
            }

            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->post($url);

            return [
                'ok' => $response->successful() || $response->status() === 409,
                'status' => $response->status(),
                'body' => $response->json() ?? [],
            ];
        } catch (ConnectionException|RequestException $e) {
            Log::warning("Admin scrape POST /sportmonks/{$path} failed", ['error' => $e->getMessage()]);

            return ['ok' => false, 'message' => 'Backend tidak merespons.'];
        }
    }

    protected function get(string $path): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->get("{$this->baseUrl}/sportmonks/{$path}");

            return $response->successful() ? ($response->json() ?? []) : [];
        } catch (ConnectionException|RequestException $e) {
            Log::warning("Admin GET /sportmonks/{$path} failed", ['error' => $e->getMessage()]);

            return [];
        }
    }
}
