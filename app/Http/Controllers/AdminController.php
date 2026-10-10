<?php

namespace App\Http\Controllers;

use App\Services\AdminService;
use App\Services\FootballPortalService;
use App\Services\NewsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(
        protected AdminService $admin,
        protected NewsService $news,
        protected FootballPortalService $football,
    ) {}

    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'running' => $this->admin->runningJobs(),
            'sync' => $this->admin->syncStatus(),
            'adminUnavailable' => $this->admin->unavailable,
        ]);
    }

    public function scrapers(): View
    {
        return view('admin.scrapers', [
            'jobs' => array_keys(AdminService::JOBS),
            'running' => $this->admin->runningJobs(),
            'sync' => [],
            'leagues' => $this->football->getLeagues(true) ?? [],
            'adminUnavailable' => $this->admin->unavailable || $this->football->unavailable,
        ]);
    }

    public function leagues(Request $request): View
    {
        $request->validate(['q' => ['nullable', 'string', 'max:200']]);
        $query = trim((string) $request->query('q', ''));
        $leagues = collect($this->football->getLeagues(false) ?? [])
            ->filter(fn ($league) => $query === '' || mb_stripos($league['name'] ?? '', $query) !== false);

        return view('admin.leagues', ['leagues' => $leagues->values()->all(), 'query' => $query, 'unavailable' => $this->football->unavailable]);
    }

    public function sync(): View
    {
        return view('admin.sync', [
            'running' => $this->admin->runningJobs(),
            'sync' => $this->admin->syncStatus(),
            'adminUnavailable' => $this->admin->unavailable,
        ]);
    }

    public function newsIndex(Request $request): View
    {
        $request->validate(['q' => ['nullable', 'string', 'max:200'], 'page' => ['nullable', 'integer', 'min:1']]);
        $query = trim((string) $request->query('q', ''));
        $items = $this->news->all();
        $filtered = collect($items ?? [])->filter(fn ($item) => $query === '' || mb_stripos(($item['title'] ?? '').' '.($item['source'] ?? ''), $query) !== false)->values();
        $page = LengthAwarePaginator::resolveCurrentPage();
        $news = new LengthAwarePaginator($filtered->forPage($page, 20)->values(), $filtered->count(), 20, $page, ['path' => $request->url(), 'query' => $request->query()]);

        return view('admin.news.index', ['news' => $news, 'query' => $query, 'unavailable' => $items === null]);
    }

    public function newsShow(int $id): View|Response
    {
        $item = $this->news->find($id);
        if ($item === false) {
            return response()->view('admin.news.unavailable', [], 503);
        }
        abort_if($item === null, 404);

        return view('admin.news.show', ['item' => $item]);
    }

    /** JSON: seasons for a league (populates the football-scrape season select). */
    public function leagueSeasons(int $id): JsonResponse
    {
        $res = $this->football->getLeagueSeasons($id);

        return response()->json(['data' => $res['data'] ?? []], $this->football->unavailable ? 503 : 200);
    }

    public function toggleLeague(Request $request): RedirectResponse
    {
        $request->validate(['league_id' => ['required', 'integer', 'min:1'], 'status' => ['required', 'boolean']]);
        $id = (int) $request->input('league_id');
        $status = $request->boolean('status');
        $ok = $this->admin->setLeagueStatus($id, $status);

        // Bust the cached league list so the dashboard reflects the change now.
        Cache::forget('football.'.md5('leagues'.serialize(['active_only' => 'false'])));
        Cache::forget('football.'.md5('leagues'.serialize(['active_only' => 'true'])));

        return back()->with(
            $ok ? 'status' : 'error',
            $ok
                ? ($status ? __('admin.flash.league_enabled') : __('admin.flash.league_disabled'))
                : __('admin.flash.league_failed')
        );
    }

    public function scrapeFootball(Request $request): RedirectResponse
    {
        $request->validate(['league_id' => ['nullable', 'integer', 'min:1'], 'season_id' => ['nullable', 'integer', 'min:1'], 'force' => ['nullable', 'boolean']]);
        $force = $request->boolean('force');
        $leagueId = $request->integer('league_id') ?: null;
        $seasonId = $request->integer('season_id') ?: null;
        $res = $this->admin->triggerFootball($force, $leagueId, $seasonId);

        if (($res['status'] ?? 0) === 409) {
            return back()->with('error', __('admin.flash.football_running'));
        }

        return back()->with(
            $res['ok'] ? 'status' : 'error',
            $res['ok'] ? __('admin.flash.football_started') : __('admin.flash.football_failed')
        );
    }

    /** JSON snapshot for the dashboard's auto-refresh (running jobs + sync). */
    public function status(): JsonResponse
    {
        return response()->json([
            'running' => $this->admin->runningJobs(),
            'sync' => $this->admin->syncStatus(),
            'unavailable' => $this->admin->unavailable,
        ]);
    }

    public function trigger(Request $request): RedirectResponse
    {
        $request->validate(['job' => ['required', Rule::in(array_keys(AdminService::JOBS))], 'force' => ['nullable', 'boolean']]);
        $job = (string) $request->input('job');
        $force = $request->boolean('force');
        $res = $this->admin->trigger($job, $force);

        if (($res['status'] ?? 0) === 409) {
            return back()->with('error', __('admin.flash.job_running', ['job' => $job]));
        }

        return back()->with(
            $res['ok'] ? 'status' : 'error',
            $res['ok']
                ? __('admin.flash.job_started', ['job' => $job])
                : ($res['message'] ?? __('admin.flash.job_failed', ['job' => $job]))
        );
    }

    public function scrapeFixture(Request $request): RedirectResponse
    {
        $id = (int) $request->input('fixture_id');
        if ($id <= 0) {
            return back()->with('error', __('admin.flash.fixture_invalid'));
        }
        $res = $this->admin->scrapeFixture($id);

        return back()->with(
            $res['ok'] ? 'status' : 'error',
            $res['ok'] ? __('admin.flash.fixture_scraped', ['id' => $id]) : __('admin.flash.fixture_failed', ['id' => $id])
        );
    }

    public function stop(string $job): RedirectResponse
    {
        abort_unless(isset(AdminService::JOBS[$job]), 404);
        $result = $this->admin->stop($job);

        return back()->with($result['ok'] ? 'status' : 'error', $result['ok'] ? __('admin.flash.job_stopping', ['job' => $job]) : __('cms.stop_failed'));
    }

    public function refreshNews(): RedirectResponse
    {
        $ok = $this->news->refresh();

        return back()->with(
            $ok ? 'status' : 'error',
            $ok ? __('admin.flash.news_refreshed') : __('admin.flash.news_failed')
        );
    }
}
