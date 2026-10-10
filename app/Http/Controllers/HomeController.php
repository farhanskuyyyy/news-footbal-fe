<?php

namespace App\Http\Controllers;

use App\Services\FootballPortalService;
use App\Services\NewsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly FootballPortalService $football,
        private readonly NewsService $news,
    ) {}

    public function index(Request $request): View
    {
        // Live is fetched fresh each load (own short cache) — the rest of the
        // dashboard (heavier, slower chain of API calls) is cached as one blob
        // so most page loads are a single cache hit instead of ~6 backend calls.
        $live = $this->football->getLiveInplay();

        $requestedLeague = filter_var($request->query('standings_league'), FILTER_VALIDATE_INT) ?: 0;
        $cacheKey = 'home:pitchticker:dashboard:single-league:'.$requestedLeague;
        $rest = Cache::remember($cacheKey, 120, fn () => $this->buildRest($requestedLeague));
        if ($rest['newsUnavailable'] || $rest['footballUnavailable']) {
            Cache::forget($cacheKey);
        }

        $matches = collect($rest['today'])->keyBy('id');
        foreach ($live as $fixture) {
            $matches->put($fixture['id'], array_replace($matches->get($fixture['id'], []), $fixture));
        }

        return view('home.index', [
            'matches' => $matches->values()->all(),
            'live' => $live,
            'today' => $rest['today'],
            'featuredLeagues' => $rest['featuredLeagues'],
            'selectedStandingsLeagueId' => $rest['selectedStandingsLeagueId'],
            'news' => $rest['news'],
            'leagues' => $rest['leagues'],
            'newsUnavailable' => $rest['newsUnavailable'],
        ]);
    }

    public function standings(Request $request): JsonResponse
    {
        $validated = $request->validate(['league_id' => ['required', 'integer', 'min:1']]);
        $leagues = $this->football->getLeagues(true) ?? [];
        abort_if($this->football->unavailable, 503);
        $league = collect($leagues)->firstWhere('id', (int) $validated['league_id']);
        abort_unless($league, 404);
        $card = $this->buildLeagueCard($league);
        abort_if($this->football->unavailable, 503);

        if ($card) {
            $params = ['league_id' => $league['id'], 'season_id' => $card['season']['id']];
            $card['links'] = [
                'standings' => route('football.index', $params + ['tab' => 'standings']),
                'topscorers' => route('football.index', $params + ['tab' => 'topscorers']),
            ];
        }

        return response()->json(['data' => $card]);
    }

    /** Builds the cacheable part of the dashboard (today, featured, news). */
    private function buildRest(int $requestedLeague): array
    {
        $newsData = $this->news->all();
        $news = collect($newsData ?? [])->take(6)->values()->all();

        // Keep the standings picker and fixture priority scoped to enabled leagues.
        $leagues = $this->football->getLeagues(true) ?? [];
        $enabledIds = array_values(array_map(static fn ($l) => (int) $l['id'], $leagues));

        $today = $this->football->prioritizeEnabledLeagues(
            $this->football->getFixturesByDate(now('Asia/Jakarta')->format('Y-m-d')),
            $enabledIds
        );

        $featuredLeagues = [];
        $selectedLeague = collect($leagues)->firstWhere('id', $requestedLeague) ?? ($leagues[0] ?? null);
        if ($selectedLeague) {
            $card = $this->buildLeagueCard($selectedLeague);
            if ($card) {
                $featuredLeagues[] = $card;
            }
        }

        return ['today' => $today, 'featuredLeagues' => $featuredLeagues, 'selectedStandingsLeagueId' => $selectedLeague['id'] ?? null, 'news' => $news, 'leagues' => $leagues, 'newsUnavailable' => $newsData === null, 'footballUnavailable' => $this->football->unavailable];
    }

    /**
     * Standings for one league's current season, or null when
     * the league has no season data yet.
     */
    private function buildLeagueCard(array $league): ?array
    {
        $seasonsData = $this->football->getLeagueSeasons((int) $league['id']);
        $seasons = $seasonsData['data'] ?? [];
        $current = collect($seasons)->firstWhere('is_current', true) ?? ($seasons[0] ?? null);

        if (! $current) {
            return null;
        }

        $sid = (int) $current['id'];
        $standings = $this->football->getSeasonStandings($sid) ?? [];

        return [
            'league' => $league,
            'season' => $current,
            'standings' => array_slice($standings, 0, 6),
        ];
    }
}
