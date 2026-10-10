@if(count($leagues))
<section id="home-standings" class="portal-card overflow-hidden" x-data="homeStandings(@js(route('home.standings')))" :aria-busy="loading" @change="if ($event.target.matches('[data-league-select]')) load($event.target.value)" @submit.prevent="load($el.querySelector('[data-league-select]').value)">
    <div class="portal-card-heading border-b border-divider p-4">
        <h2 class="mb-3 text-base font-semibold">{{ __('home.featured.standings') }}</h2>
        <form action="{{ route('home') }}#home-standings" method="GET">
            <label for="standings-league" class="sr-only">{{ __('home.featured.select_league') }}</label>
            <x-league-select id="standings-league" name="standings_league" :leagues="$leagues" :selected="$selectedStandingsLeagueId" />
            <noscript><button class="mt-2 w-full rounded-lg border border-control px-3 text-sm text-link">{{ __('home.featured.show_standings') }}</button></noscript>
        </form>
    </div>
    <div class="p-4" x-show="!ready">
        @forelse($featuredLeagues as $fl)
            <div data-standings-league="{{ $fl['league']['id'] }}">
                <p class="mb-2 text-xs text-muted">{{ $fl['season']['name'] }}</p>
                <table class="w-full text-left text-sm">
                    <thead class="text-xs text-muted"><tr><th scope="col" class="py-2 pr-3">{{ __('home.featured.position') }}</th><th scope="col" class="py-2">{{ __('home.featured.club') }}</th><th scope="col" class="py-2 text-right">{{ __('home.featured.points') }}</th></tr></thead>
                    <tbody>
                        @forelse($fl['standings'] as $standing)
                            <tr class="border-t border-divider"><td class="py-3 pr-3 tabular-nums text-muted">{{ $standing['position'] ?? $loop->iteration }}</td><th scope="row" class="py-3 pr-3 font-medium"><span class="flex items-center gap-2"><span class="entity-mark shrink-0" aria-hidden="true">@if(!empty($standing['team']['image_path']))<img src="{{ $standing['team']['image_path'] }}" alt="" loading="lazy">@endif<x-icon name="shield" class="h-5 w-5 text-muted" /></span><span class="min-w-0 break-words">{{ $standing['team']['name'] ?? '—' }}</span></span></th><td class="py-3 text-right font-semibold tabular-nums">{{ $standing['points'] ?? '—' }}</td></tr>
                        @empty <tr><td colspan="3" class="py-4 text-sm text-muted">{{ __('home.featured.standings_empty') }}</td></tr> @endforelse
                    </tbody>
                </table>
                <a href="{{ route('football.index', ['league_id' => $fl['league']['id'], 'season_id' => $fl['season']['id'], 'tab' => 'standings']) }}" class="mt-2 flex min-h-11 items-center text-xs font-semibold text-link">{{ __('home.featured.standings_full') }}</a>
                <a href="{{ route('football.index', ['league_id' => $fl['league']['id'], 'season_id' => $fl['season']['id'], 'tab' => 'topscorers']) }}" class="flex min-h-11 items-center border-t border-divider text-xs text-link">{{ __('home.featured.topscorers_full') }}</a>
            </div>
        @empty <p class="text-sm text-muted">{{ __('home.featured.standings_empty') }}</p> @endforelse
    </div>
    <div class="p-4" x-show="ready" x-cloak>
        <div x-show="loading">
            <p role="status" class="sr-only">{{ __('home.featured.loading') }}</p>
            <div aria-hidden="true" class="space-y-4">
                <span class="skeleton-block h-4 w-24"></span>
                @foreach(range(1, 6) as $row)<div class="flex items-center gap-3 py-2"><span class="skeleton-block h-5 w-5"></span><span class="skeleton-block h-4 flex-1"></span><span class="skeleton-block h-4 w-6"></span></div>@endforeach
            </div>
        </div>
        <div x-show="failed" role="alert">
            <p class="text-sm text-danger">{{ __('home.featured.failed') }}</p>
            <button type="button" @click="load($el.closest('section').querySelector('[data-league-select]').value)" class="cms-button mt-4">{{ __('home.featured.retry') }}</button>
        </div>
        <p x-show="!loading && !failed && !card" class="text-sm text-muted">{{ __('home.featured.standings_empty') }}</p>
        <template x-if="!loading && !failed && card">
            <div :data-standings-league="card.league.id">
                <p class="mb-2 text-xs text-muted" x-text="card.season.name"></p>
                <table class="w-full text-left text-sm">
                    <thead class="text-xs text-muted"><tr><th scope="col" class="py-2 pr-3">{{ __('home.featured.position') }}</th><th scope="col" class="py-2">{{ __('home.featured.club') }}</th><th scope="col" class="py-2 text-right">{{ __('home.featured.points') }}</th></tr></thead>
                    <tbody>
                        <template x-for="(standing, index) in card.standings" :key="index">
                            <tr class="border-t border-divider"><td class="py-3 pr-3 tabular-nums text-muted" x-text="standing.position ?? index + 1"></td><th scope="row" class="py-3 pr-3 font-medium"><span class="flex items-center gap-2"><span class="entity-mark shrink-0" aria-hidden="true"><template x-if="standing.team?.image_path"><img :src="standing.team.image_path" alt="" loading="lazy"></template><x-icon name="shield" class="h-5 w-5 text-muted" /></span><span class="min-w-0 break-words" x-text="standing.team?.name ?? '—'"></span></span></th><td class="py-3 text-right font-semibold tabular-nums" x-text="standing.points ?? '—'"></td></tr>
                        </template>
                        <tr x-show="!card.standings.length"><td colspan="3" class="py-4 text-sm text-muted">{{ __('home.featured.standings_empty') }}</td></tr>
                    </tbody>
                </table>
                <a :href="card.links.standings" class="mt-2 flex min-h-11 items-center text-xs font-semibold text-link">{{ __('home.featured.standings_full') }}</a>
                <a :href="card.links.topscorers" class="flex min-h-11 items-center border-t border-divider text-xs text-link">{{ __('home.featured.topscorers_full') }}</a>
            </div>
        </template>
    </div>
</section>
@endif
