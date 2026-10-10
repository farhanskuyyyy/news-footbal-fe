<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CmsTest extends TestCase
{
    use RefreshDatabase;

    private bool $upstreamUnavailable = false;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        $fixture = require __DIR__.'/../Fixtures/portal.php';
        Http::fake(function ($request) use ($fixture) {
            if ($this->upstreamUnavailable) {
                return Http::response([], 503);
            }
            $path = parse_url($request->url(), PHP_URL_PATH);

            return Http::response(['data' => match ($path) {
                '/portal/leagues' => [$fixture['league']],
                '/news' => [$fixture['news']],
                '/news/1' => $fixture['news'],
                default => [],
            }]);
        });
    }

    public function test_cms_pages_and_account_mutations_require_authentication(): void
    {
        foreach (['/admin', '/admin/users', '/admin/news', '/admin/leagues', '/admin/scrapers', '/admin/sync'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
        $this->post('/admin/users', [])->assertRedirect('/login');
        $this->put('/admin/users/1', [])->assertRedirect('/login');
        $this->delete('/admin/users/1')->assertRedirect('/login');
    }

    public function test_cms_pages_use_the_sidebar_and_keep_public_navigation_out(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['/admin', '/admin/users', '/admin/users/create', '/admin/news', '/admin/news/1', '/admin/leagues', '/admin/scrapers', '/admin/sync'] as $url) {
            $this->get($url)->assertOk()->assertSee('cms-sidebar')->assertSee(route('admin.users.index'))->assertDontSee('href="'.route('football.live').'"', false);
        }
    }

    public function test_user_crud_hashes_passwords_preserves_blank_password_and_blocks_self_delete(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Test account', 'email' => 'account@example.invalid',
            'password' => 'Test-password-123', 'password_confirmation' => 'Test-password-123',
        ])->assertRedirect(route('admin.users.index'));
        $account = User::where('email', 'account@example.invalid')->firstOrFail();
        $this->assertTrue(Hash::check('Test-password-123', $account->password));
        $hash = $account->password;
        $this->put('/admin/users/'.$account->id, ['name' => 'Renamed account', 'email' => $account->email, 'password' => ''])->assertRedirect(route('admin.users.index'));
        $this->assertSame($hash, $account->fresh()->password);
        $this->assertSame('Renamed account', $account->fresh()->name);
        $this->delete('/admin/users/'.$admin->id)->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->delete('/admin/users/'.$account->id)->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $account->id]);
    }

    public function test_user_validation_and_password_update(): void
    {
        $admin = User::factory()->create();
        $account = User::factory()->create();
        $this->actingAs($admin)->post('/admin/users', ['name' => '', 'email' => $admin->email, 'password' => 'short', 'password_confirmation' => 'different'])->assertSessionHasErrors(['name', 'email', 'password']);
        $this->put('/admin/users/'.$account->id, ['name' => $account->name, 'email' => $admin->email])->assertSessionHasErrors('email');
        $this->put('/admin/users/'.$account->id, ['name' => $account->name, 'email' => $account->email, 'password' => 'Changed-password-123', 'password_confirmation' => 'Changed-password-123'])->assertRedirect(route('admin.users.index'));
        $this->assertTrue(Hash::check('Changed-password-123', $account->fresh()->password));
    }

    public function test_account_search_and_news_empty_error_states(): void
    {
        $admin = User::factory()->create(['name' => 'Searchable Admin']);
        $this->actingAs($admin)->get('/admin/users?q=SEARCHABLE')->assertOk()->assertSee($admin->email);
        $this->get('/admin/users?q=nonexistent')->assertOk()->assertSee(__('cms.users_empty'));
        $this->get('/admin/news?q=nonexistent')->assertOk()->assertSee(__('cms.news_empty'));
        Cache::flush();
        $this->upstreamUnavailable = true;
        $this->get('/admin/leagues')->assertOk()->assertSee(__('common.empty.unavailable'));
        $this->get('/admin/leagues/5/seasons')->assertServiceUnavailable();
        $this->get('/admin/news/2')->assertServiceUnavailable()->assertSee(__('cms.news_unavailable'));
    }

    public function test_league_and_scrape_actions_forward_existing_backend_parameters(): void
    {
        $this->actingAs(User::factory()->create())->from('/admin/leagues')->post('/admin/leagues/toggle', ['league_id' => 5, 'status' => 1])->assertRedirect('/admin/leagues')->assertSessionHas('status');
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/portal/leagues/5/status') && $request['status'] === true);
        $this->from('/admin/scrapers')->post('/admin/scrape/football', ['league_id' => 5, 'season_id' => 10, 'force' => 1])->assertRedirect('/admin/scrapers');
        Http::assertSent(fn ($request) => str_contains($request->url(), '/sportmonks/scrape/football?force=true&league_id=5&season_id=10'));
    }

    public function test_invalid_actions_and_failed_stop_do_not_report_success(): void
    {
        $this->actingAs(User::factory()->create())->post('/admin/leagues/toggle', ['league_id' => -1, 'status' => 'invalid'])->assertSessionHasErrors(['league_id', 'status']);
        $this->post('/admin/scrape', ['job' => 'invalid'])->assertSessionHasErrors('job');
        $this->upstreamUnavailable = true;
        $this->post('/admin/scrape/stop/football')->assertSessionHas('error', __('cms.stop_failed'));
        $this->post('/admin/scrape/stop/unknown')->assertNotFound();
    }
}
