<?php

namespace App\Http\Controllers;

use App\Services\FootballPortalService;
use Illuminate\Http\JsonResponse;
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
        // Public portal only lists enabled (status) + Sportmonks-active leagues.
        $leagues = $this->footballService->getLeagues(true) ?? [];

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
            // Fixture not in DB yet — show a loading page that triggers an
            // on-demand scrape (see prepareFixture), then reloads.
            return view('football.fixture_loading', ['fixtureId' => $id]);
        }

        // Head-to-head + predictions from the live Sportmonks proxy (not persisted).
        $homeId = $data['home_team']['id'] ?? null;
        $awayId = $data['away_team']['id'] ?? null;
        $h2h = ($homeId && $awayId) ? $this->footballService->getHeadToHead($homeId, $awayId) : [];
        $predictions = $this->footballService->getFixturePredictions($id);
        $odds = $this->footballService->getFixtureOdds($id);

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
            'h2h' => $h2h,
            'predictions' => $predictions,
            'odds' => $odds,
        ]);
    }

    /**
     * Triggered by the loading page (AJAX): scrape the fixture on demand, then
     * report whether it's now available so the client can redirect.
     */
    public function prepareFixture(int $id): JsonResponse
    {
        $this->footballService->scrapeFixture($id);
        $ready = $this->footballService->getFixtureDetail($id) !== null;

        return response()->json(['ready' => $ready]);
    }

    public function live(): View
    {
        $matches = $this->footballService->getLiveInplay();

        return view('football.live', [
            'matches' => $matches,
        ]);
    }

    public function matchday(Request $request): View
    {
        $date = $request->query('date', date('Y-m-d'));
        // Guard the date format; fall back to today on anything unexpected.
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        if (! $d || $d->format('Y-m-d') !== $date) {
            $date = date('Y-m-d');
        }

        $fixtures = $this->footballService->getFixturesByDate($date);

        return view('football.matchday', [
            'date' => $date,
            'fixtures' => $fixtures,
        ]);
    }

    public function search(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $type = $request->query('type', 'teams');
        $results = strlen($q) >= 2 ? $this->footballService->search($type, $q) : [];

        return view('football.search', [
            'q' => $q,
            'type' => in_array($type, ['teams', 'players', 'leagues'], true) ? $type : 'teams',
            'results' => $results,
        ]);
    }

    public function teamDetail(int $id, Request $request): View
    {
        $seasonId = $request->integer('season_id') ?: null;
        $data = $this->footballService->getTeamDetail($id, $seasonId);

        if (! $data) {
            abort(404, 'Klub tidak ditemukan.');
        }

        // Upcoming + recent fixtures fetched live from the Sportmonks proxy.
        $upcoming = $this->footballService->getTeamUpcoming($id, 5);
        $recent = $this->footballService->getTeamRecent($id, 6);

        return view('football.team', [
            'team' => $data['team'] ?? [],
            'venue' => $data['venue'] ?? null,
            'coach' => $data['coach'] ?? null,
            'squads' => $data['squads'] ?? [],
            'players' => $data['players'] ?? [],
            'positions' => $data['positions'] ?? [],
            'rivals' => $data['rivals'] ?? [],
            'upcoming' => $upcoming,
            'recent' => $recent,
            'seasonId' => $seasonId,
        ]);
    }

    public function transfers(): View
    {
        return view('football.transfers', [
            'transfers' => $this->footballService->getLatestTransfers(40),
        ]);
    }

    public function compare(Request $request): View
    {
        $p1 = $request->integer('p1') ?: null;
        $p2 = $request->integer('p2') ?: null;

        return view('football.compare', [
            'p1id' => $p1,
            'p2id' => $p2,
            'player1' => $p1 ? $this->footballService->getPlayerDetail($p1) : null,
            'player2' => $p2 ? $this->footballService->getPlayerDetail($p2) : null,
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
            'position' => $data['position'] ?? null,
            'detailedPosition' => $data['detailed_position'] ?? null,
            'squads' => $data['squads'] ?? [],
            'teams' => $data['teams'] ?? [],
            'clubHistory' => $data['club_history'] ?? [],
            'transfers' => $data['transfers'] ?? [],
            'topscorers' => $data['topscorers'] ?? [],
            'statistics' => $data['statistics'] ?? [],
        ]);
    }
}
