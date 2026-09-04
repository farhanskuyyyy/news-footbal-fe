@extends('layouts.app')

@section('title', ($team['name'] ?? 'Klub') . ' - Profil & Skuad Pemain')

@section('content')
<div class="space-y-8">

    {{-- Breadcrumb --}}
    <div>
        <a href="{{ url()->previous() ?? route('football.index') }}" class="text-xs font-bold text-slate-400 hover:text-emerald-400 transition-colors flex items-center gap-1.5 bg-slate-900 border border-slate-800 px-3.5 py-2 rounded-xl inline-flex">
            &larr; Kembali ke Daftar Klub & Klasemen
        </a>
    </div>

    {{-- Team Header Card --}}
    <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 rounded-3xl border border-slate-800 shadow-2xl p-6 sm:p-10 relative overflow-hidden">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-8 relative z-10">
            <div class="w-28 h-28 p-4 bg-slate-950 rounded-3xl border border-slate-800 flex items-center justify-center shadow-2xl flex-shrink-0">
                @if(!empty($team['image_path']))
                    <img src="{{ $team['image_path'] }}" alt="{{ $team['name'] }}" class="max-h-full max-w-full object-contain filter drop-shadow-xl">
                @else
                    <span class="text-5xl">🛡️</span>
                @endif
            </div>

            <div class="flex-1 text-center sm:text-left space-y-3">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3">
                    <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight">{{ $team['name'] }}</h1>
                    @if(!empty($team['short_code']))
                        <span class="px-3 py-1 rounded-full text-xs font-mono font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 uppercase tracking-widest">
                            {{ $team['short_code'] }}
                        </span>
                    @endif
                </div>

                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-5 text-xs text-slate-400 font-medium pt-1">
                    @if(!empty($team['founded']))
                        <span class="flex items-center gap-1.5">📅 Berdiri: <strong class="text-slate-200">{{ $team['founded'] }}</strong></span>
                    @endif
                    @if($venue)
                        <span class="flex items-center gap-1.5">📍 Stadion: <strong class="text-slate-200">{{ $venue['name'] }}</strong> ({{ $venue['city_name'] ?? '' }})</span>
                        @if(!empty($venue['capacity']))
                            <span class="flex items-center gap-1.5">👥 Kapasitas: <strong class="text-slate-200">{{ number_format($venue['capacity']) }}</strong></span>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Coach + Rivals row --}}
    @if($coach || !empty($rivals))
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            {{-- Head Coach card --}}
            @if($coach)
                <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-5 shadow-xl flex items-center gap-4">
                    @if(!empty($coach['image_path']))
                        <img src="{{ $coach['image_path'] }}" alt="{{ $coach['name'] ?? '' }}" class="h-16 w-16 rounded-2xl object-cover border-2 border-emerald-500/40 bg-slate-950">
                    @else
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-800 text-2xl border border-slate-700">🧑‍💼</div>
                    @endif
                    <div class="min-w-0">
                        <span class="kicker block text-[10px] font-bold uppercase text-emerald-400">Pelatih Kepala</span>
                        <h3 class="text-base font-black text-white truncate mt-0.5">{{ $coach['display_name'] ?? $coach['name'] ?? 'Pelatih' }}</h3>
                        @if(!empty($coach['date_of_birth']))
                            <p class="text-[11px] text-slate-500 font-mono">{{ date_diff(date_create($coach['date_of_birth']), date_create('today'))->y }} thn</p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Rivals chips --}}
            @if(!empty($rivals))
                <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-5 shadow-xl {{ $coach ? 'lg:col-span-2' : 'lg:col-span-3' }}">
                    <span class="kicker block text-[10px] font-bold uppercase text-slate-400 mb-3">🔥 Rival / Derby</span>
                    <div class="flex flex-wrap gap-2">
                        @foreach($rivals as $rv)
                            <a href="{{ route('football.team', $rv['id']) }}?season_id={{ $seasonId }}"
                               class="group flex items-center gap-2 rounded-full border border-slate-800 bg-slate-950 pl-1.5 pr-3.5 py-1.5 hover:border-red-500/50 transition-colors">
                                @if(!empty($rv['image_path']))
                                    <img src="{{ $rv['image_path'] }}" alt="" class="h-6 w-6 object-contain">
                                @else
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-800 text-xs">🛡️</span>
                                @endif
                                <span class="text-xs font-bold text-slate-300 group-hover:text-white transition-colors">{{ $rv['name'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Stadium / Venue card --}}
    @if($venue)
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl overflow-hidden shadow-xl flex flex-col sm:flex-row">
            @if(!empty($venue['image_path']))
                <div class="relative sm:w-1/3 h-40 sm:h-auto bg-slate-950">
                    <img src="{{ $venue['image_path'] }}" alt="{{ $venue['name'] ?? '' }}" class="h-full w-full object-cover" loading="lazy" onerror="this.style.display='none'">
                    <div class="absolute inset-0 bg-gradient-to-t sm:bg-gradient-to-r from-slate-900/80 to-transparent"></div>
                </div>
            @endif
            <div class="flex-1 p-6">
                <span class="kicker block text-[10px] font-bold uppercase text-emerald-400 mb-2">🏟️ Markas / Stadion</span>
                <h3 class="text-lg font-black text-white">{{ $venue['name'] ?? '-' }}</h3>
                @if(!empty($venue['address']))
                    <p class="text-xs text-slate-400 mt-1">{{ $venue['address'] }}</p>
                @endif
                <div class="mt-4 grid grid-cols-3 gap-3">
                    <div class="rounded-2xl border border-slate-800 bg-slate-950/80 p-3">
                        <span class="block text-[10px] uppercase font-bold text-slate-500">Kota</span>
                        <span class="text-sm font-extrabold text-white truncate block">{{ $venue['city_name'] ?? '-' }}</span>
                    </div>
                    <div class="rounded-2xl border border-slate-800 bg-slate-950/80 p-3">
                        <span class="block text-[10px] uppercase font-bold text-slate-500">Kapasitas</span>
                        <span class="text-sm font-extrabold text-white font-mono block">{{ !empty($venue['capacity']) ? number_format($venue['capacity']) : '-' }}</span>
                    </div>
                    <div class="rounded-2xl border border-slate-800 bg-slate-950/80 p-3">
                        <span class="block text-[10px] uppercase font-bold text-slate-500">Permukaan</span>
                        <span class="text-sm font-extrabold text-white capitalize truncate block">{{ $venue['surface'] ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Recent results + form (live from Sportmonks proxy) --}}
    @if(!empty($recent))
        @php
            $tid = $team['id'] ?? 0;
            $formArr = [];
            foreach ($recent as $f) {
                $loc = null;
                foreach (($f['participants'] ?? []) as $p) {
                    if (($p['id'] ?? 0) == $tid) { $loc = $p['meta']['location'] ?? null; break; }
                }
                if (! $loc) continue;
                $mine = null; $opp = null;
                foreach (($f['scores'] ?? []) as $s) {
                    if (($s['description'] ?? '') === 'CURRENT') {
                        $pp = $s['score']['participant'] ?? '';
                        $g = $s['score']['goals'] ?? null;
                        if ($pp === $loc) $mine = $g; else $opp = $g;
                    }
                }
                if ($mine === null || $opp === null) continue;
                $formArr[] = $mine > $opp ? 'W' : ($mine < $opp ? 'L' : 'D');
            }
            $formStrip = array_reverse($formArr); // oldest → newest
        @endphp

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M4 7h16M4 12h10M4 17h7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </span>
                    <div>
                        <span class="kicker block text-[10px] font-bold uppercase text-emerald-400">Performa</span>
                        <h2 class="text-lg font-black text-white">Hasil Terakhir</h2>
                    </div>
                </div>
                @if(!empty($formStrip))
                    <div class="flex items-center gap-1">
                        @foreach($formStrip as $r)
                            @php $cls = match($r) { 'W' => 'bg-emerald-500/90 text-slate-950', 'D' => 'bg-slate-600 text-white', 'L' => 'bg-red-500/90 text-white', default => 'bg-slate-800' }; @endphp
                            <span class="flex h-6 w-6 items-center justify-center rounded text-[11px] font-black font-mono {{ $cls }}">{{ $r }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                @foreach($recent as $f)
                    @include('football.partials.live-card', ['f' => $f])
                @endforeach
            </div>
        </div>
    @endif

    {{-- Upcoming fixtures (live from Sportmonks proxy) --}}
    @if(!empty($upcoming))
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><rect x="4" y="5.5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M4 9.5h16M8 3.5v4M16 3.5v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                </span>
                <div>
                    <span class="kicker block text-[10px] font-bold uppercase text-emerald-400">Jadwal</span>
                    <h2 class="text-lg font-black text-white">5 Pertandingan Berikutnya</h2>
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                @foreach($upcoming as $f)
                    @include('football.partials.live-card', ['f' => $f])
                @endforeach
            </div>
        </div>
    @endif

    {{-- Squad / Players Section --}}
    <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div>
                <h2 class="text-lg font-black text-white flex items-center gap-2">👥 Skuad Pemain</h2>
                <p class="text-xs text-slate-400 mt-0.5">Daftar pemain resmi yang terdaftar di klub ini. Klik pemain untuk melihat detail profil.</p>
            </div>
            <span class="text-xs font-mono font-bold px-3.5 py-1.5 bg-slate-950 text-emerald-400 border border-slate-800 rounded-xl">
                {{ count($players) }} Pemain
            </span>
        </div>

        @php
            // Map player_id -> squad row (jersey number, captain flag) from the squads payload
            $squadByPlayer = [];
            foreach (($squads ?? []) as $sq) {
                if (!empty($sq['player_id'])) {
                    $squadByPlayer[$sq['player_id']] = $sq;
                }
            }
            // Sort players by jersey number (unnumbered last)
            $sortedPlayers = collect($players)->sortBy(function ($p) use ($squadByPlayer) {
                return $squadByPlayer[$p['id']]['jersey_number'] ?? 999;
            })->values()->all();
        @endphp

        @if(count($players) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($sortedPlayers as $p)
                    @php
                        $sq = $squadByPlayer[$p['id']] ?? null;
                        $jersey = $sq['jersey_number'] ?? null;
                        $isCaptain = !empty($sq['captain']);
                    @endphp
                    <a href="{{ route('football.player', $p['id']) }}" class="group relative p-4 rounded-2xl border border-slate-800 bg-slate-950/80 hover:bg-slate-850 hover:border-emerald-500/50 hover:shadow-xl transition-all flex items-center gap-3.5">
                        @if($isCaptain)
                            <span title="Kapten" class="absolute top-2.5 right-2.5 flex h-5 w-5 items-center justify-center rounded-full bg-amber-400 text-slate-950 text-[10px] font-black shadow">C</span>
                        @endif

                        <div class="relative flex-shrink-0">
                            @if(!empty($p['image_path']))
                                <img src="{{ $p['image_path'] }}" alt="{{ $p['name'] }}" class="w-12 h-12 rounded-full object-cover border-2 border-slate-700 bg-slate-900 group-hover:border-emerald-400 transition-colors">
                            @else
                                <div class="w-12 h-12 rounded-full bg-slate-800 text-slate-300 flex items-center justify-center font-bold text-sm border border-slate-700">
                                    👤
                                </div>
                            @endif
                            @if($jersey !== null)
                                <span class="absolute -bottom-1 -left-1 flex h-5 min-w-[20px] px-1 items-center justify-center rounded-md bg-emerald-500 text-slate-950 text-[10px] font-black font-mono border border-slate-950 shadow">{{ $jersey }}</span>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <h4 class="font-extrabold text-white text-sm truncate group-hover:text-emerald-400 transition-colors">{{ $p['display_name'] ?? $p['name'] }}</h4>
                            <p class="text-[11px] text-slate-400 font-semibold truncate mt-0.5">
                                @php
                                    $pid = $p['position_id'] ?? 0;
                                    $posIcon = match($pid) { 24 => '🧤', 25 => '🛡️', 26 => '⚙️', 27 => '⚡', default => '⚽' };
                                    // Prefer the resolved Type-dictionary name; fall back to Indonesian labels
                                    $posLabel = ($positions[$pid] ?? null) ?: match($pid) { 24 => 'Kiper', 25 => 'Bek', 26 => 'Gelandang', 27 => 'Penyerang', default => 'Pemain' };
                                @endphp
                                {{ $posIcon }} {{ $posLabel }}
                                @if(!empty($p['date_of_birth']))
                                    • {{ date_diff(date_create($p['date_of_birth']), date_create('today'))->y }} thn
                                @endif
                            </p>
                            @if(!empty($p['height']))
                                <span class="text-[10px] text-slate-500 font-mono block">{{ $p['height'] }} cm / {{ $p['weight'] ?? '-' }} kg</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="p-12 text-center text-slate-400 text-sm">
                Belum ada data pemain detail pada tim ini.
            </div>
        @endif
    </div>

</div>
@endsection
