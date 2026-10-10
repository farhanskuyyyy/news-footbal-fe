<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LandingTest extends TestCase
{
    private bool $upstreamFails = false;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Http::preventStrayRequests();
        Http::fake(['*' => fn () => $this->upstreamFails ? Http::response([], 503) : Http::response(['data' => []])]);
    }

    public function test_first_visit_shows_landing_without_a_visit_cookie(): void
    {
        $this->get('/')->assertOk()->assertSee(__('landing.heading_first'))->assertSee(route('home'))->assertCookieMissing('pitchticker_seen');
    }

    public function test_returning_browser_still_sees_the_landing(): void
    {
        $this->withCookie('pitchticker_seen', '1')->get('/')->assertOk()->assertSee(__('landing.heading_first'));
    }

    public function test_direct_dashboard_visit_does_not_redirect_or_set_a_visit_cookie(): void
    {
        $this->get('/home')->assertOk()->assertCookieMissing('pitchticker_seen');
        $this->withCookie('pitchticker_seen', '1')->get('/home')->assertOk();
    }

    public function test_landing_survives_upstream_failure(): void
    {
        $this->upstreamFails = true;
        $this->get('/')->assertOk()->assertSee(__('landing.coverage_empty'));
    }
}
