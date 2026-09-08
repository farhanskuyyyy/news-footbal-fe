@extends('layouts.app')

@section('title', __('admin.title'))

@section('content')
    <div class="space-y-8" x-data="adminPanel(@js($running), @js($sync))" x-init="poll()">

        {{-- Header --}}
        <div class="relative overflow-hidden border border-line rounded-xl p-6 sm:p-8 relative overflow-hidden">
            <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="kicker block text-xs font-bold uppercase text-primary mb-2">{{ __('admin.kicker') }}</span>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">{{ __('admin.heading') }}</h1>
                    <p class="text-xs text-body mt-1">{{ __('admin.subheading') }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-xl border border-line bg-ink px-4 py-2 text-sm font-bold text-white hover:text-white hover:border-line transition-colors">{{ __('common.actions.logout') }}</button>
                </form>
            </div>
        </div>

        {{-- Flash --}}
        @if (session('status'))
            <p class="rounded-xl border border-line px-4 py-3 text-sm font-medium text-accent">{{ session('status') }}</p>
        @endif
        @if (session('error'))
            <p class="rounded-xl border border-line px-4 py-3 text-sm font-medium text-accent">{{ session('error') }}</p>
        @endif

        {{-- Running jobs (live) --}}
        <div class="rounded-xl border border-line bg-surface p-6">
            <div class="flex items-center gap-2 mb-4">
                <span class="relative flex h-2.5 w-2.5">
                    <span class=" absolute inline-flex h-full w-full rounded-lg bg-primary opacity-75"></span>
                    <span class="relative inline-flex rounded-lg h-2.5 w-2.5 bg-primary"></span>
                </span>
                <h2 class="text-base font-bold text-white">{{ __('admin.jobs.heading') }}</h2>
                <span class="text-xs font-mono text-muted">{{ __('admin.jobs.auto_refresh') }}</span>
            </div>
            <template x-if="running.length === 0">
                <p class="text-sm text-muted">{{ __('admin.jobs.empty') }}</p>
            </template>
            <div class="flex flex-wrap gap-2">
                <template x-for="job in running" :key="job">
                    <div class="flex items-center gap-2 rounded-xl border border-line px-3 py-2">
                        <span class="text-sm font-bold text-accent font-mono" x-text="job"></span>
                        <form method="POST" :action="`{{ url('/admin/scrape/stop') }}/${job}`">
                            @csrf
                            <button class="rounded-lg bg-accent hover:bg-accent px-2.5 py-1 text-xs font-bold text-white transition-colors">{{ __('admin.jobs.stop') }}</button>
                        </form>
                    </div>
                </template>
            </div>
        </div>

        {{-- Scrape triggers --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="rounded-xl border border-line bg-surface p-6 space-y-4">
                <h2 class="text-base font-bold text-white">{{ __('admin.scraper.heading') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($jobs as $job)
                        <form method="POST" action="{{ route('admin.scrape') }}" class="rounded-xl border border-line bg-ink p-3.5">
                            @csrf
                            <input type="hidden" name="job" value="{{ $job }}">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-sm font-bold text-white font-mono">{{ $job }}</span>
                                <button class="rounded-lg bg-primary hover:bg-accent px-3 py-1.5 text-xs font-bold text-white transition-colors">{{ __('admin.scraper.run') }}</button>
                            </div>
                            @if(in_array($job, ['football', 'fixture-details']))
                                <label class="mt-2 flex items-center gap-1.5 text-xs text-muted">
                                    <input type="checkbox" name="force" value="1" class="rounded-lg border-line bg-ink text-accent focus:ring-line">
                                    {{ __('admin.scraper.force_ttl') }}
                                </label>
                            @endif
                        </form>
                    @endforeach
                </div>

                {{-- Single fixture --}}
                <form method="POST" action="{{ route('admin.scrape.fixture') }}" class="flex items-center gap-2 rounded-xl border border-line bg-ink p-3">
                    @csrf
                    <input type="number" name="fixture_id" placeholder="{{ __('admin.scraper.fixture_id') }}" required
                           class="flex-1 rounded-lg border border-line bg-surface px-3 py-2 text-sm font-mono text-white placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-line">
                    <button class="rounded-lg bg-surface hover:bg-surface px-3.5 py-2 text-xs font-bold text-white transition-colors whitespace-nowrap">{{ __('admin.scraper.scrape_fixture') }}</button>
                </form>
            </div>

            {{-- News --}}
            <div class="rounded-xl border border-line bg-surface p-6 space-y-4">
                <h2 class="text-base font-bold text-white">{{ __('admin.news.heading') }}</h2>
                <p class="text-sm text-body">{{ __('admin.news.description') }}</p>
                <form method="POST" action="{{ route('admin.news.refresh') }}">
                    @csrf
                    <button class="inline-flex items-center gap-2 rounded-xl bg-primary hover:bg-accent px-4 py-2.5 text-sm font-bold text-white transition-all">
                        <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4"><path d="M4 12a8 8 0 0113.7-5.6M20 12a8 8 0 01-13.7 5.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M17 3v3.5h-3.5M7 21v-3.5h3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        {{ __('admin.news.refresh') }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Scrape Football: pilih liga & musim --}}
        <div class="rounded-xl border border-line bg-surface p-6" x-data="footballScrape()">
            <h2 class="text-base font-bold text-white mb-1">{{ __('admin.football.heading') }}</h2>
            <p class="text-xs text-body mb-4">{{ __('admin.football.description') }}</p>
            <form method="POST" action="{{ route('admin.scrape.football') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                @csrf
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-body">{{ __('admin.football.league') }}</label>
                    <select name="league_id" x-model="leagueId" @change="loadSeasons()"
                            class="w-full rounded-xl border border-line bg-ink px-3 py-2.5 text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-line">
                        <option value="">{{ __('admin.football.all_leagues') }}</option>
                        @foreach($leagues as $lg)
                            @if(!empty($lg['status']))
                                <option value="{{ $lg['id'] }}">{{ $lg['name'] }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-body">{{ __('admin.football.season') }}</label>
                    <select name="season_id" x-model="seasonId" :disabled="!leagueId || loading"
                            class="w-full rounded-xl border border-line bg-ink px-3 py-2.5 text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-line disabled:opacity-50">
                        <option value="">{{ __('admin.football.current_season') }}</option>
                        <template x-for="s in seasons" :key="s.id">
                            <option :value="s.id" x-text="s.name + (s.is_current ? @js(__('admin.football.season_current_suffix')) : '')"></option>
                        </template>
                    </select>
                </div>
                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-1.5 text-xs text-body">
                        <input type="checkbox" name="force" value="1" class="rounded-lg border-line bg-ink text-accent focus:ring-line">
                        {{ __('admin.scraper.force') }}
                    </label>
                    <button class="flex-1 rounded-xl bg-primary hover:bg-accent px-4 py-2.5 text-sm font-bold text-white transition-colors">{{ __('admin.scraper.run') }}</button>
                </div>
            </form>
        </div>

        {{-- League management (CMS) --}}
        <div class="rounded-xl border border-line bg-surface p-6">
            <h2 class="text-base font-bold text-white mb-1">{{ __('admin.leagues.heading') }}</h2>
            <p class="text-xs text-body mb-4">{!! __('admin.leagues.description', ['strong' => '<strong class="text-accent">'.e(__('admin.leagues.description_strong')).'</strong>']) !!}</p>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm whitespace-nowrap">
                    <thead class="bg-ink text-body font-bold uppercase tracking-wider border-b border-line text-xs">
                        <tr>
                            <th class="py-3 px-3">{{ __('admin.leagues.th.league') }}</th>
                            <th class="py-3 px-3 text-center">{{ __('admin.leagues.th.sportmonks_active') }}</th>
                            <th class="py-3 px-3 text-center">{{ __('admin.leagues.th.seasons') }}</th>
                            <th class="py-3 px-3 text-center">{{ __('admin.leagues.th.scrape_status') }}</th>
                            <th class="py-3 px-3 text-center">{{ __('admin.leagues.th.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-white">
                        @forelse($leagues as $lg)
                            <tr class="hover:bg-surface">
                                <td class="py-2.5 px-3 font-bold text-white">
                                    <span class="flex items-center gap-2">
                                        @if(!empty($lg['image_path']))
                                            <img src="{{ $lg['image_path'] }}" alt="" class="w-5 h-5 object-contain">
                                        @endif
                                        {{ $lg['name'] }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded-lg text-xs font-bold uppercase {{ !empty($lg['active']) ? 'text-accent border border-line' : 'bg-surface text-body' }}">
                                        {{ !empty($lg['active']) ? __('admin.leagues.yes') : __('admin.leagues.no') }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-center font-mono text-body">{{ $lg['seasons_count'] ?? 0 }}</td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded-lg text-xs font-bold uppercase {{ !empty($lg['status']) ? 'text-accent border border-line' : 'text-accent border border-line' }}">
                                        {{ !empty($lg['status']) ? __('admin.leagues.enabled') : __('admin.leagues.disabled') }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <form method="POST" action="{{ route('admin.leagues.toggle') }}">
                                        @csrf
                                        <input type="hidden" name="league_id" value="{{ $lg['id'] }}">
                                        <input type="hidden" name="status" value="{{ !empty($lg['status']) ? '0' : '1' }}">
                                        <button class="rounded-lg px-3 py-1 text-xs font-bold transition-colors {{ !empty($lg['status']) ? 'bg-accent hover:bg-accent text-white' : 'bg-primary hover:bg-accent text-white' }}">
                                            {{ !empty($lg['status']) ? __('admin.leagues.disable') : __('admin.leagues.enable') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 text-center text-muted">{!! __('admin.leagues.empty', ['job' => '<span class="font-mono">leagues</span>']) !!}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Sync status table --}}
        <div class="rounded-xl border border-line bg-surface p-6">
            <h2 class="text-base font-bold text-white mb-4">{{ __('admin.sync.heading') }}</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm whitespace-nowrap">
                    <thead class="bg-ink text-body font-bold uppercase tracking-wider border-b border-line text-xs">
                        <tr>
                            <th class="py-3 px-3">{{ __('admin.sync.th.table') }}</th>
                            <th class="py-3 px-3">{{ __('admin.sync.th.last_sync') }}</th>
                            <th class="py-3 px-3 text-center">{{ __('admin.sync.th.records') }}</th>
                            <th class="py-3 px-3 text-center">{{ __('admin.sync.th.status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-white">
                        <template x-for="row in sync" :key="row.table_name">
                            <tr class="hover:bg-surface">
                                <td class="py-2.5 px-3 font-bold font-mono text-white" x-text="row.table_name"></td>
                                <td class="py-2.5 px-3 text-body font-mono text-xs" x-text="fmt(row.latest_synced_at)"></td>
                                <td class="py-2.5 px-3 text-center font-mono" x-text="row.records_synced ?? 0"></td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded-lg text-xs font-bold uppercase tracking-wider"
                                          :class="{
 'text-accent border border-line': row.status === 'success',
                                            'text-accent border border-line': row.status === 'failed',
                                            'text-steel border border-line': row.status === 'in_progress',
                                            'bg-surface text-white': !['success','failed','in_progress'].includes(row.status)
                                          }" x-text="row.status || '-'"></span>
                                </td>
                            </tr>
                        </template>
                        <template x-if="sync.length === 0">
                            <tr><td colspan="4" class="py-6 text-center text-muted">{{ __('admin.sync.empty') }}</td></tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function footballScrape() {
            return {
                leagueId: '',
                seasonId: '',
                seasons: [],
                loading: false,
                async loadSeasons() {
                    this.seasonId = '';
                    this.seasons = [];
                    if (!this.leagueId) return;
                    this.loading = true;
                    try {
                        const res = await fetch(`{{ url('/admin/leagues') }}/${this.leagueId}/seasons`, { headers: { 'Accept': 'application/json' } });
                        const data = await res.json();
                        this.seasons = data.data || [];
                    } catch (e) { /* ignore */ }
                    this.loading = false;
                },
            }
        }

        function adminPanel(initialRunning, initialSync) {
            return {
                running: initialRunning || [],
                sync: initialSync || [],
                fmt(v) {
                    if (!v) return '-';
                    try {
                        return new Date(v).toLocaleString(@js(str_replace('_', '-', app()->getLocale())), { timeZone: 'Asia/Jakarta', dateStyle: 'short', timeStyle: 'short' }) + ' WIB';
                    } catch (e) { return v; }
                },
                async poll() {
                    setInterval(async () => {
                        try {
                            const res = await fetch('{{ route('admin.status') }}', { headers: { 'Accept': 'application/json' } });
                            const data = await res.json();
                            this.running = data.running || [];
                            this.sync = data.sync || [];
                        } catch (e) { /* ignore transient errors */ }
                    }, 5000);
                },
            }
        }
    </script>
@endsection
