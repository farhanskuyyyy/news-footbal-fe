<?php

namespace App\Http\Controllers;

use App\Services\AdminService;
use App\Services\FootballPortalService;
use App\Services\NewsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
            'jobs' => array_keys(AdminService::JOBS),
            'running' => $this->admin->runningJobs(),
            'sync' => $this->admin->syncStatus(),
            'leagues' => $this->football->getLeagues(false) ?? [],
        ]);
    }

    /** JSON: seasons for a league (populates the football-scrape season select). */
    public function leagueSeasons(int $id): JsonResponse
    {
        $res = $this->football->getLeagueSeasons($id);

        return response()->json(['data' => $res['data'] ?? []]);
    }

    public function toggleLeague(Request $request): RedirectResponse
    {
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
        ]);
    }

    public function trigger(Request $request): RedirectResponse
    {
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
        $this->admin->stop($job);

        return back()->with('status', __('admin.flash.job_stopping', ['job' => $job]));
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
