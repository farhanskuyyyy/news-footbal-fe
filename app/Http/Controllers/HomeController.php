<?php

namespace App\Http\Controllers;

use App\Services\FootballPortalService;
use App\Services\NewsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
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
            'featured' => $rest['featured'],
            'news' => $rest['news'],
        ]);
    }

    /** Builds the cacheable part of the dashboard (today, featured, news). */
    private function buildRest(): array
    {
        $today = $this->football->getFixturesByDate(date('Y-m-d'));
        $news = collect($this->news->all() ?? [])->take(6)->values()->all();

        $leagues = $this->football->getLeagues(true) ?? [];
        $featured = null;
        if (! empty($leagues)) {
            $lg = $leagues[0];
            $seasonsData = $this->football->getLeagueSeasons((int) $lg['id']);
            $seasons = $seasonsData['data'] ?? [];
            $current = collect($seasons)->firstWhere('is_current', true) ?? ($seasons[0] ?? null);

            if ($current) {
                $sid = (int) $current['id'];
                $standings = $this->football->getSeasonStandings($sid) ?? [];
                $ts = $this->football->getSeasonTopscorers($sid);
                $featured = [
                    'league' => $lg,
                    'season' => $current,
                    'standings' => array_slice($standings, 0, 6),
                    'topscorers' => array_slice($ts['data'] ?? [], 0, 5),
                ];
            }
        }

        return ['today' => $today, 'featured' => $featured, 'news' => $news];
    }
}
