<?php

namespace App\Http\Controllers;

use App\Services\FootballPortalService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FootballController extends Controller
{
    protected FootballPortalService $footballService;

    public function __construct(FootballPortalService $footballService)
    {
        $this->footballService = $footballService;
    }

    public function index(Request $request): View
    {
        $leagues = $this->footballService->getLeagues() ?? [];

        // Select league: from query or default to first available league
        $selectedLeagueId = $request->integer('league_id');
        if (! $selectedLeagueId && count($leagues) > 0) {
            $selectedLeagueId = $leagues[0]['id'];
        }

        $seasonsData = $selectedLeagueId ? $this->footballService->getLeagueSeasons($selectedLeagueId) : null;
        $seasons = $seasonsData['data'] ?? [];
        $selectedLeague = $seasonsData['league'] ?? null;

        // Select season: from query or default to current / first season
        $selectedSeasonId = $request->integer('season_id');
        if (! $selectedSeasonId && count($seasons) > 0) {
            // Pick current season if available, else first
            $currentSeason = collect($seasons)->firstWhere('is_current', true) ?? $seasons[0];
            $selectedSeasonId = $currentSeason['id'];
        }

        $activeTab = $request->query('tab', 'standings');
        $overview = null;
        $standings = [];
        $rounds = [];
        $fixtures = [];
        $teams = [];
        $topscorers = [];
        $availableTypes = [];
        $selectedTypeId = $request->integer('type_id') ?: null;
        $transfers = [];
        $selectedRoundId = $request->integer('round_id') ?: null;

        if ($selectedSeasonId) {
            $overview = $this->footballService->getSeasonOverview($selectedSeasonId);

            switch ($activeTab) {
                case 'fixtures':
                    $rounds = $this->footballService->getSeasonRounds($selectedSeasonId) ?? [];
                    $fixtures = $this->footballService->getSeasonFixtures($selectedSeasonId, $selectedRoundId) ?? [];
                    break;
                case 'teams':
                    $teams = $this->footballService->getSeasonTeams($selectedSeasonId) ?? [];
                    break;
                case 'topscorers':
                    $tsPayload = $this->footballService->getSeasonTopscorers($selectedSeasonId, $selectedTypeId);
                    $topscorers = $tsPayload['data'] ?? [];
                    $availableTypes = $tsPayload['available_types'] ?? [];
                    $selectedTypeId = $tsPayload['selected_type_id'] ?? $selectedTypeId;
                    break;
                case 'transfers':
                    $transfers = $this->footballService->getSeasonTransfers($selectedSeasonId) ?? [];
                    break;
                case 'standings':
                default:
                    $standings = $this->footballService->getSeasonStandings($selectedSeasonId) ?? [];
                    break;
            }
        }

        return view('football.index', compact(
            'leagues',
            'selectedLeague',
            'selectedLeagueId',
            'seasons',
            'selectedSeasonId',
            'activeTab',
            'overview',
            'standings',
            'rounds',
            'selectedRoundId',
            'fixtures',
            'teams',
            'topscorers',
            'availableTypes',
            'selectedTypeId',
            'transfers'
        ));
    }

    public function fixtureDetail(int $id): View
    {
        $data = $this->footballService->getFixtureDetail($id);

        if (! $data) {
            abort(404, 'Pertandingan tidak ditemukan.');
        }

        return view('football.fixture', [
            'fixture' => $data['fixture'] ?? [],
            'league' => $data['league'] ?? null,
            'season' => $data['season'] ?? null,
            'venue' => $data['venue'] ?? null,
            'home_team' => $data['home_team'] ?? null,
            'away_team' => $data['away_team'] ?? null,
            'events' => $data['events'] ?? [],
            'lineups' => $data['lineups'] ?? [],
            'home_lineup' => $data['home_lineup'] ?? null,
            'away_lineup' => $data['away_lineup'] ?? null,
            'statistics' => $data['statistics'] ?? [],
            'scores' => $data['scores'] ?? [],
            'referees' => $data['referees'] ?? [],
        ]);
    }

    public function teamDetail(int $id, Request $request): View
    {
        $seasonId = $request->integer('season_id') ?: null;
        $data = $this->footballService->getTeamDetail($id, $seasonId);

        if (! $data) {
            abort(404, 'Klub tidak ditemukan.');
        }

        return view('football.team', [
            'team' => $data['team'] ?? [],
            'venue' => $data['venue'] ?? null,
            'squads' => $data['squads'] ?? [],
            'players' => $data['players'] ?? [],
            'rivals' => $data['rivals'] ?? [],
            'seasonId' => $seasonId,
        ]);
    }

    public function playerDetail(int $id): View
    {
        $data = $this->footballService->getPlayerDetail($id);

        if (! $data) {
            abort(404, 'Data pemain tidak ditemukan.');
        }

        return view('football.player', [
            'player' => $data['player'] ?? [],
            'country' => $data['country'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'squads' => $data['squads'] ?? [],
            'teams' => $data['teams'] ?? [],
            'transfers' => $data['transfers'] ?? [],
            'topscorers' => $data['topscorers'] ?? [],
        ]);
    }
}
