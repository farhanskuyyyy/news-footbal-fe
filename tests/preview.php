<?php

use App\Models\User;
use App\Services\RabbitMQService;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

// Run with: php -S 127.0.0.1:8099 -t public tests/preview.php
if (PHP_SAPI !== 'cli-server' || ! in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit;
}
$public = realpath(__DIR__.'/../public');
$asset = realpath($public.parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if ($asset && str_starts_with($asset, $public.'/') && is_file($asset)) {
    return false;
}
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->instance('request', Request::capture());
$app->make(Kernel::class)->bootstrap();
config(['app.env' => 'testing', 'app.debug' => true, 'cache.default' => 'array', 'session.driver' => 'file', 'session.files' => sys_get_temp_dir().'/pitchticker-preview-sessions', 'database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'news.api_url' => 'http://fixture.invalid']);
if (! is_dir(config('session.files'))) {
    mkdir(config('session.files'), 0700, true);
}
$f = require __DIR__.'/Fixtures/portal.php';
$app->bind(RabbitMQService::class, fn () => new class extends RabbitMQService
{
    public function publishImageUpload(array $payload): bool
    {
        return false;
    }
});
config(['filesystems.disks.public.root' => sys_get_temp_dir().'/pitchticker-preview-uploads']);
if (isset($_GET['preview_baseline']) && is_dir('/tmp/pitchticker-baseline-views')) {
    config(['view.paths' => ['/tmp/pitchticker-baseline-views']]);
    $app['view']->getFinder()->setPaths(['/tmp/pitchticker-baseline-views']);
}
View::share('previewMode', true);
Http::preventStrayRequests();
Http::fake(function ($request) use ($f) {
    if (($_GET['preview_state'] ?? '') === 'error') {
        return Http::response([], 503);
    }
    if (($_GET['preview_state'] ?? '') === 'empty') {
        return Http::response(['data' => []]);
    }
    $path = parse_url($request->url(), PHP_URL_PATH);
    $data = [];
    if ($path === '/portal/fixtures/999') {
        return Http::response([], 404);
    }
    if ($path === '/sportmonks/scrape/jobs' && ($_GET['preview_state'] ?? '') === 'running') {
        return Http::response(['running' => ['football']]);
    }
    if ($path === '/sportmonks/sync/status') {
        return Http::response(['data' => [['table_name' => 'fixture_table', 'status' => 'failed', 'records_synced' => 0, 'latest_synced_at' => '2026-10-09T05:00:00Z']]]);
    }
    if ($path === '/portal/leagues') {
        $data = [$f['league']];
    } elseif (str_ends_with($path, '/seasons')) {
        return Http::response(['league' => $f['league'], 'data' => [['id' => 5, 'name' => 'Fixture 2026/27', 'is_current' => true]]]);
    } elseif (str_ends_with($path, '/overview')) {
        $data = ['total_teams' => 2, 'total_fixtures' => 1, 'total_rounds' => 1, 'season' => ['name' => 'Fixture 2026/27']];
    } elseif (str_ends_with($path, '/standings')) {
        $data = [['position' => 1, 'team' => $f['home'], 'played' => 1, 'won' => 0, 'draw' => 1, 'lost' => 0, 'goal_difference' => 0, 'points' => 1]];
    } elseif (str_ends_with($path, '/topscorers')) {
        $data = [['player' => $f['player'], 'total' => 0]];
    } elseif (str_starts_with($path, '/portal/fixtures/')) {
        $data = ['fixture' => $f['fixture'], 'home_team' => $f['home'], 'away_team' => $f['away'], 'league' => $f['league'], 'scores' => [['description' => 'CURRENT', 'home_goals' => 0, 'away_goals' => 0]], 'events' => [], 'lineups' => [], 'statistics' => []];
    } elseif (str_starts_with($path, '/portal/teams/')) {
        $data = ['team' => $f['home'], 'players' => [], 'positions' => [], 'squads' => []];
    } elseif (str_starts_with($path, '/portal/players/')) {
        $data = ['player' => $f['player']];
    } elseif (str_contains($path, '/fixtures') || str_contains($path, 'livescores/inplay')) {
        $data = [$f['fixture']];
        if (str_contains($path, 'livescores/inplay')) {
            $data[0]['state'] = ['short_name' => 'HT', 'name' => 'Half-time'];
        }
        if (($_GET['preview_state'] ?? '') === 'long') {
            $data[0]['participants'][0]['name'] = 'Fixture United with a deliberately long football club name';
            $data[0]['home_team']['name'] = $data[0]['participants'][0]['name'];
        }
    } elseif ($path === '/news') {
        $data = array_map(fn ($id) => array_replace($f['news'], ['id' => $id]), range(1, 30));
    } elseif ($path === '/news/1') {
        $data = $f['news'];
    } elseif (str_contains($path, '/search/')) {
        $data = [$f['home']];
    } elseif (str_ends_with($path, '/teams')) {
        $data = [$f['home'], $f['away']];
    }

    return Http::response(['data' => $data]);
});
if (str_starts_with($_SERVER['REQUEST_URI'], '/admin')) {
    $previewDatabase = sys_get_temp_dir().'/pitchticker-cms-preview.sqlite';
    if (! is_file($previewDatabase)) {
        touch($previewDatabase);
        chmod($previewDatabase, 0600);
    }
    config(['database.connections.sqlite.database' => $previewDatabase]);
    if (! Schema::hasTable('users')) {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }
    Auth::setUser(User::firstOrCreate(['email' => 'fixture@example.invalid'], ['name' => 'Fixture Admin', 'password' => Hash::make('fixture-preview-password')]));
}
$app->handleRequest(Request::capture());
