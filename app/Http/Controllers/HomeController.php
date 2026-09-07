<?php

namespace App\Http\Controllers;

use App\Services\FootballPortalService;
use App\Services\NewsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    /** Sportmonks type id for the "Goal Topscorer" ranking. */
    private const GOAL_TOPSCORER_TYPE = 208;

    /** How many enabled leagues get a standings/topscorer card on the home page. */
    private const MAX_FEATURED_LEAGUES = 6;

    public function __construct(
        private readonly FootballPortalService $football,
        private readonly NewsService $news,
    ) {}

    public function index(): View
    {
        // Live is fetched fresh each load (own short cache) — the rest of the
        // dashboard (heavier, slower chain of API calls) is cached as one blob
        // so most page loads are a single cache hit instead of ~6 backend calls.
        $live = collect($this->football->getLiveInplay())->take(8)->values()->all();

        $rest = Cache::remember('home:dashboard', 120, fn () => $this->buildRest());

        return view('home.index', [
            'live' => $live,
            'today' => $rest['today'],
            'featuredLeagues' => $rest['featuredLeagues'],
            'news' => $rest['news'],
        ]);
    }

    /** Builds the cacheable part of the dashboard (today, featured, news). */
    private function buildRest(): array
    {
        $news = collect($this->news->all() ?? [])->take(6)->values()->all();

        // Only CMS-enabled leagues (status = true) feed the standings/topscorer
        // cards, and their fixtures are pushed to the top of today's list.
        $leagues = $this->football->getLeagues(true) ?? [];
        $enabledIds = array_values(array_map(static fn ($l) => (int) $l['id'], $leagues));

        $today = $this->football->prioritizeEnabledLeagues(
            $this->football->getFixturesByDate(date('Y-m-d')),
            $enabledIds
        );

        $featuredLeagues = [];
        foreach (array_slice($leagues, 0, self::MAX_FEATURED_LEAGUES) as $lg) {
            $card = $this->buildLeagueCard($lg);
            if ($card) {
                $featuredLeagues[] = $card;
            }
        }

        return ['today' => $today, 'featuredLeagues' => $featuredLeagues, 'news' => $news];
    }

    /**
     * Standings + goal topscorers for one league's current season, or null when
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
        $ts = $this->football->getSeasonTopscorers($sid, self::GOAL_TOPSCORER_TYPE);

        return [
            'league' => $league,
            'season' => $current,
            'standings' => array_slice($standings, 0, 6),
            'topscorers' => array_slice($ts['data'] ?? [], 0, 5),
        ];
    }
}
