<?php

namespace App\Http\Controllers;

use App\Services\FootballPortalService;
use App\Services\NewsService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly FootballPortalService $football,
        private readonly NewsService $news,
    ) {}

    public function index(): View
    {
        // Live + today's matches (live proxy).
        $live = collect($this->football->getLiveInplay())->take(8)->values()->all();
        $today = $this->football->getFixturesByDate(date('Y-m-d'));

        // Featured league snapshot: first active league → current season →
        // top standings + top scorers.
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

        // Latest news (few).
        $newsItems = collect($this->news->all() ?? [])->take(6)->values()->all();

        return view('home.index', [
            'live' => $live,
            'today' => $today,
            'featured' => $featured,
            'news' => $newsItems,
        ]);
    }
}
