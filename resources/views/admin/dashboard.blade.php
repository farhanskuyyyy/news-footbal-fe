@extends('layouts.app')

@section('title', 'Admin Dashboard - KREASIBALL')

@section('content')
    <div class="space-y-8" x-data="adminPanel(@js($running), @js($sync))" x-init="poll()">

        {{-- Header --}}
        <div class="pitch-stripes bg-gradient-to-r from-slate-900 via-slate-900/90 to-slate-950 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="kicker block text-[10px] font-bold uppercase text-emerald-400 mb-2">Panel Admin</span>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">Kelola Scraper</h1>
                    <p class="text-xs text-slate-400 mt-1">Sportmonks &amp; Berita — trigger, pantau, hentikan job.</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-xl border border-slate-800 bg-slate-950 px-4 py-2 text-sm font-bold text-slate-300 hover:text-white hover:border-slate-700 transition-colors">Keluar</button>
                </form>
            </div>
        </div>

        {{-- Flash --}}
        @if (session('status'))
            <p class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-300">{{ session('status') }}</p>
        @endif
        @if (session('error'))
            <p class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm font-medium text-red-300">{{ session('error') }}</p>
        @endif

        {{-- Running jobs (live) --}}
        <div class="rounded-3xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl">
            <div class="flex items-center gap-2 mb-4">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                <h2 class="text-base font-black text-white">Job Berjalan</h2>
                <span class="text-[10px] font-mono text-slate-500">(auto-refresh 5s)</span>
            </div>
            <template x-if="running.length === 0">
                <p class="text-sm text-slate-500">Tidak ada job yang berjalan.</p>
            </template>
            <div class="flex flex-wrap gap-2">
                <template x-for="job in running" :key="job">
                    <div class="flex items-center gap-2 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-3 py-2">
                        <span class="text-sm font-bold text-emerald-300 font-mono" x-text="job"></span>
                        <form method="POST" :action="`{{ url('/admin/scrape/stop') }}/${job}`">
                            @csrf
                            <button class="rounded-lg bg-red-500/90 hover:bg-red-500 px-2.5 py-1 text-[11px] font-black text-white transition-colors">Stop</button>
                        </form>
                    </div>
                </template>
            </div>
        </div>

        {{-- Scrape triggers --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="rounded-3xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl space-y-4">
                <h2 class="text-base font-black text-white">Sportmonks Scraper</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($jobs as $job)
                        <form method="POST" action="{{ route('admin.scrape') }}" class="rounded-2xl border border-slate-800 bg-slate-950 p-3.5">
                            @csrf
                            <input type="hidden" name="job" value="{{ $job }}">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-sm font-bold text-slate-200 font-mono">{{ $job }}</span>
                                <button class="rounded-lg bg-emerald-500 hover:bg-emerald-400 px-3 py-1.5 text-xs font-black text-slate-950 transition-colors">Run</button>
                            </div>
                            @if(in_array($job, ['football', 'fixture-details']))
                                <label class="mt-2 flex items-center gap-1.5 text-[11px] text-slate-500">
                                    <input type="checkbox" name="force" value="1" class="rounded border-slate-700 bg-slate-950 text-emerald-500 focus:ring-emerald-500">
                                    force (bypass TTL)
                                </label>
                            @endif
                        </form>
                    @endforeach
                </div>

                {{-- Single fixture --}}
                <form method="POST" action="{{ route('admin.scrape.fixture') }}" class="flex items-center gap-2 rounded-2xl border border-slate-800 bg-slate-950 p-3">
                    @csrf
                    <input type="number" name="fixture_id" placeholder="Fixture ID" required
                           class="flex-1 rounded-lg border border-slate-800 bg-slate-900 px-3 py-2 text-sm font-mono text-white placeholder:text-slate-600 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <button class="rounded-lg bg-slate-800 hover:bg-slate-700 px-3.5 py-2 text-xs font-bold text-white transition-colors whitespace-nowrap">Scrape Fixture</button>
                </form>
            </div>

            {{-- News --}}
            <div class="rounded-3xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl space-y-4">
                <h2 class="text-base font-black text-white">Berita</h2>
                <p class="text-sm text-slate-400">Tarik berita terbaru dari sumber (NewsAPI via backend).</p>
                <form method="POST" action="{{ route('admin.news.refresh') }}">
                    @csrf
                    <button class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 px-4 py-2.5 text-sm font-bold text-slate-950 shadow-lg shadow-emerald-500/20 transition-all">
                        <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4"><path d="M4 12a8 8 0 0113.7-5.6M20 12a8 8 0 01-13.7 5.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M17 3v3.5h-3.5M7 21v-3.5h3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Refresh Berita
                    </button>
                </form>
            </div>
        </div>

        {{-- Scrape Football: pilih liga & musim --}}
        <div class="rounded-3xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl" x-data="footballScrape()">
            <h2 class="text-base font-black text-white mb-1">Scrape Football (Pilih Liga &amp; Musim)</h2>
            <p class="text-xs text-slate-400 mb-4">Kosongkan musim untuk pakai musim aktif liga. Kosongkan liga untuk semua liga aktif.</p>
            <form method="POST" action="{{ route('admin.scrape.football') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                @csrf
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-400">Liga</label>
                    <select name="league_id" x-model="leagueId" @change="loadSeasons()"
                            class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2.5 text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">— Semua liga aktif —</option>
                        @foreach($leagues as $lg)
                            @if(!empty($lg['status']))
                                <option value="{{ $lg['id'] }}">{{ $lg['name'] }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-400">Musim</label>
                    <select name="season_id" x-model="seasonId" :disabled="!leagueId || loading"
                            class="w-full rounded-xl border border-slate-800 bg-slate-950 px-3 py-2.5 text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:opacity-50">
                        <option value="">— Musim aktif —</option>
                        <template x-for="s in seasons" :key="s.id">
                            <option :value="s.id" x-text="s.name + (s.is_current ? ' (aktif)' : '')"></option>
                        </template>
                    </select>
                </div>
                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-1.5 text-[11px] text-slate-400">
                        <input type="checkbox" name="force" value="1" class="rounded border-slate-700 bg-slate-950 text-emerald-500 focus:ring-emerald-500">
                        force
                    </label>
                    <button class="flex-1 rounded-xl bg-emerald-500 hover:bg-emerald-400 px-4 py-2.5 text-sm font-black text-slate-950 transition-colors">Run</button>
                </div>
            </form>
        </div>

        {{-- League management (CMS) --}}
        <div class="rounded-3xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl">
            <h2 class="text-base font-black text-white mb-1">Manajemen Liga</h2>
            <p class="text-xs text-slate-400 mb-4">Aktifkan/nonaktifkan liga untuk di-scrape. Hanya liga <strong class="text-emerald-400">Aktif</strong> yang diproses saat scrape football (tanpa filter liga).</p>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm whitespace-nowrap">
                    <thead class="bg-slate-950/80 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-800 text-[11px]">
                        <tr>
                            <th class="py-3 px-3">Liga</th>
                            <th class="py-3 px-3 text-center">Sportmonks Active</th>
                            <th class="py-3 px-3 text-center">Musim</th>
                            <th class="py-3 px-3 text-center">Status Scrape</th>
                            <th class="py-3 px-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-200">
                        @forelse($leagues as $lg)
                            <tr class="hover:bg-slate-800/40">
                                <td class="py-2.5 px-3 font-bold text-white">
                                    <span class="flex items-center gap-2">
                                        @if(!empty($lg['image_path']))
                                            <img src="{{ $lg['image_path'] }}" alt="" class="w-5 h-5 object-contain">
                                        @endif
                                        {{ $lg['name'] }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase {{ !empty($lg['active']) ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-slate-700 text-slate-400' }}">
                                        {{ !empty($lg['active']) ? 'Ya' : 'Tidak' }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-center font-mono text-slate-400">{{ $lg['seasons_count'] ?? 0 }}</td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase {{ !empty($lg['status']) ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-red-500/20 text-red-300 border border-red-500/30' }}">
                                        {{ !empty($lg['status']) ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <form method="POST" action="{{ route('admin.leagues.toggle') }}">
                                        @csrf
                                        <input type="hidden" name="league_id" value="{{ $lg['id'] }}">
                                        <input type="hidden" name="status" value="{{ !empty($lg['status']) ? '0' : '1' }}">
                                        <button class="rounded-lg px-3 py-1 text-[11px] font-bold transition-colors {{ !empty($lg['status']) ? 'bg-red-500/90 hover:bg-red-500 text-white' : 'bg-emerald-500 hover:bg-emerald-400 text-slate-950' }}">
                                            {{ !empty($lg['status']) ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 text-center text-slate-500">Belum ada liga. Jalankan scrape <span class="font-mono">leagues</span> dulu.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Sync status table --}}
        <div class="rounded-3xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl">
            <h2 class="text-base font-black text-white mb-4">Status Sinkronisasi</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm whitespace-nowrap">
                    <thead class="bg-slate-950/80 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-800 text-[11px]">
                        <tr>
                            <th class="py-3 px-3">Tabel</th>
                            <th class="py-3 px-3">Terakhir Sync</th>
                            <th class="py-3 px-3 text-center">Records</th>
                            <th class="py-3 px-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-200">
                        <template x-for="row in sync" :key="row.table_name">
                            <tr class="hover:bg-slate-800/40">
                                <td class="py-2.5 px-3 font-bold font-mono text-white" x-text="row.table_name"></td>
                                <td class="py-2.5 px-3 text-slate-400 font-mono text-[11px]" x-text="fmt(row.latest_synced_at)"></td>
                                <td class="py-2.5 px-3 text-center font-mono" x-text="row.records_synced ?? 0"></td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider"
                                          :class="{
                                            'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30': row.status === 'success',
                                            'bg-red-500/20 text-red-300 border border-red-500/30': row.status === 'failed',
                                            'bg-blue-500/20 text-blue-300 border border-blue-500/30': row.status === 'in_progress',
                                            'bg-slate-700 text-slate-300': !['success','failed','in_progress'].includes(row.status)
                                          }" x-text="row.status || '-'"></span>
                                </td>
                            </tr>
                        </template>
                        <template x-if="sync.length === 0">
                            <tr><td colspan="4" class="py-6 text-center text-slate-500">Belum ada data sinkronisasi.</td></tr>
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
                        return new Date(v).toLocaleString('id-ID', { timeZone: 'Asia/Jakarta', dateStyle: 'short', timeStyle: 'short' }) + ' WIB';
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
