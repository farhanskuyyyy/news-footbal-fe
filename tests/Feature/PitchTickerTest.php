<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PitchTickerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Http::preventStrayRequests();
    }

    public function test_match_rows_preserve_zero_scores_and_convert_utc_to_wib(): void
    {
        $data = require __DIR__.'/../Fixtures/portal.php';
        Http::fake(['*/portal/leagues*' => Http::response(['data' => [$data['league']]]), '*/sportmonks/fixtures/date/*' => Http::response(['data' => [$data['fixture']]])]);
        $this->get('/football/matches?date=2026-10-09')->assertOk()->assertSee('PitchTicker')->assertSee('19:00 WIB')->assertSee('aria-label="0 - 0"', false)->assertDontSee('line-through');
    }

    public function test_ajax_keeps_enabled_league_before_earlier_kickoff(): void
    {
        $data = require __DIR__.'/../Fixtures/portal.php';
        $other = $data['fixture'];
        $other['id'] = 101;
        $other['league'] = ['id' => 2, 'name' => 'Other fixture league'];
        $other['league_id'] = 2;
        $other['starting_at'] = '2026-10-09 01:00:00';
        Http::fake(['*/portal/leagues*' => Http::response(['data' => [$data['league']]]), '*/sportmonks/fixtures/date/*' => Http::response(['data' => [$other, $data['fixture']]])]);
        $this->get('/football/matches?date=2026-10-09', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertSeeInOrder(['Fixture Premier League', 'Other fixture league'])->assertDontSee('<html', false);
    }

    public function test_upstream_error_is_distinguished_from_an_empty_schedule(): void
    {
        Http::fake(['*/portal/leagues*' => Http::response(['data' => []]), '*/sportmonks/fixtures/date/*' => Http::response([], 503)]);
        $this->get('/football/matches?date=2026-10-09')->assertOk()->assertSee(__('common.empty.unavailable'))->assertDontSee(__('football.matchday.empty'));
    }

    public function test_missing_scores_are_not_displayed_as_zero_zero(): void
    {
        $data = require __DIR__.'/../Fixtures/portal.php';
        $data['fixture']['scores'] = [];
        unset($data['fixture']['current_home_score'], $data['fixture']['current_away_score']);
        Http::fake(['*/portal/leagues*' => Http::response(['data' => []]), '*/sportmonks/fixtures/date/*' => Http::response(['data' => [$data['fixture']]])]);
        $this->get('/football/matches?date=2026-10-09')->assertOk()->assertDontSee('aria-label="0 - 0"', false)->assertSee('—');
    }

    public function test_invalid_date_input_falls_back_to_today_in_wib(): void
    {
        Http::fake(['*' => Http::response(['data' => []])]);
        foreach (['2026-02-30', 'invalid', ['invalid']] as $date) {
            $this->get('/football/matches?'.http_build_query(['date' => $date]))->assertOk()->assertSee('data-date="'.now('Asia/Jakarta')->format('Y-m-d').'"', false);
        }
    }

    public function test_unavailable_fixture_does_not_trigger_the_prepare_screen(): void
    {
        Http::fake(['*' => Http::response([], 503)]);
        $this->get('/football/fixtures/100')->assertServiceUnavailable()->assertSee(__('common.empty.unavailable'))->assertDontSee('fixtureLoader');
        Http::assertNotSent(fn ($request) => $request->method() === 'POST');
    }

    public function test_participant_fallback_and_live_state_are_retained(): void
    {
        $data = require __DIR__.'/../Fixtures/portal.php';
        $fixture = $data['fixture'];
        unset($fixture['home_team'], $fixture['away_team']);
        $fixture['participants'] = [$data['home']];
        $fixture['state']['short_name'] = 'HT';
        Http::fake(['*/portal/leagues*' => Http::response(['data' => []]), '*/sportmonks/livescores/inplay*' => Http::response(['data' => [$fixture]])]);
        $this->get('/football/live')->assertOk()->assertSee('Fixture United')->assertSee('Sample City')->assertSee('HT')->assertSee('text-danger');
    }

    public function test_homepage_does_not_silently_clip_today_matches(): void
    {
        $data = require __DIR__.'/../Fixtures/portal.php';
        $fixtures = array_map(fn ($id) => array_replace($data['fixture'], ['id' => $id]), range(100, 112));
        Http::fake([
            '*/sportmonks/fixtures/date/*' => Http::response(['data' => $fixtures]),
            '*' => Http::response(['data' => []]),
        ]);
        $response = $this->get('/')->assertOk()->assertSee('PitchTicker');
        $this->assertSame(13, substr_count($response->getContent(), 'data-match-row'));
        $response->assertSee(route('football.fixture', 112));
    }

    public function test_admin_status_reports_upstream_failure_without_exposing_a_false_empty_state(): void
    {
        Http::fake(['*' => Http::response([], 503)]);
        $user = new User(['id' => 1, 'name' => 'Fixture admin']);
        $this->actingAs($user)->get('/admin/status')->assertOk()->assertJsonPath('unavailable', true);
        $this->get('/admin')->assertOk()->assertSee(__('common.empty.admin_unavailable'));
    }
}
