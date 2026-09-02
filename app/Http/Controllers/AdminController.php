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
                ? ('Liga '.($status ? 'diaktifkan' : 'dinonaktifkan').'.')
                : 'Gagal mengubah status liga.'
        );
    }

    public function scrapeFootball(Request $request): RedirectResponse
    {
        $force = $request->boolean('force');
        $leagueId = $request->integer('league_id') ?: null;
        $seasonId = $request->integer('season_id') ?: null;
        $res = $this->admin->triggerFootball($force, $leagueId, $seasonId);

        if (($res['status'] ?? 0) === 409) {
            return back()->with('error', 'Job football masih berjalan — hentikan dulu sebelum menjalankan ulang.');
        }

        return back()->with(
            $res['ok'] ? 'status' : 'error',
            $res['ok'] ? 'Scrape football dijalankan (background).' : 'Gagal menjalankan scrape football.'
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
            return back()->with('error', "Job '{$job}' masih berjalan — hentikan dulu sebelum menjalankan ulang.");
        }

        return back()->with(
            $res['ok'] ? 'status' : 'error',
            $res['ok']
                ? "Job '{$job}' dijalankan (background)."
                : ($res['message'] ?? "Gagal menjalankan job '{$job}'.")
        );
    }

    public function scrapeFixture(Request $request): RedirectResponse
    {
        $id = (int) $request->input('fixture_id');
        if ($id <= 0) {
            return back()->with('error', 'Fixture ID tidak valid.');
        }
        $res = $this->admin->scrapeFixture($id);

        return back()->with(
            $res['ok'] ? 'status' : 'error',
            $res['ok'] ? "Fixture {$id} berhasil di-scrape." : "Gagal scrape fixture {$id}."
        );
    }

    public function stop(string $job): RedirectResponse
    {
        $this->admin->stop($job);

        return back()->with('status', "Menghentikan job '{$job}'.");
    }

    public function refreshNews(): RedirectResponse
    {
        $ok = $this->news->refresh();

        return back()->with(
            $ok ? 'status' : 'error',
            $ok ? 'Berita berhasil di-refresh.' : 'Gagal refresh berita.'
        );
    }
}
