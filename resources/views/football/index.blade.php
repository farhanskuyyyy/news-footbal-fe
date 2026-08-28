@extends('layouts.app')

@section('title', 'KREASIBALL - Pusat Jadwal, Skor Langsung & Klasemen')

@section('content')
<div class="space-y-8">

    {{-- HEADER & LEAGUE SELECTOR --}}
    <div class="pitch-stripes bg-gradient-to-r from-slate-900 via-slate-900/90 to-slate-950 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
        {{-- Background Glow --}}
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="kicker inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> MATCHDAY PORTAL
                    </span>
                    <span class="text-xs text-slate-400">Database Real-time Football</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white flex items-center gap-3">
                    @if($selectedLeague && !empty($selectedLeague['image_path']))
                        <img src="{{ $selectedLeague['image_path'] }}" alt="{{ $selectedLeague['name'] }}" class="w-9 h-9 object-contain filter drop-shadow">
                    @endif
                    <span>{{ $selectedLeague['name'] ?? 'Pilih Liga Sepak Bola' }}</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-xl">
                    Pantau statistik pertandingan, formasi 22 pemain langsung di satu lapangan, skor babak, dan bursa transfer terkini.
                </p>
            </div>

            {{-- Select Dropdown Inputs (League & Season) --}}
            <div class="flex flex-wrap items-center gap-3 bg-slate-950/90 p-3 rounded-2xl border border-slate-800 self-start md:self-auto shadow-xl">
                {{-- League Select --}}
                <div class="flex items-center gap-2">
                    <label for="leagueSelect" class="text-xs font-bold uppercase tracking-wider text-slate-400">Liga:</label>
                    <select id="leagueSelect"
                            onchange="location.href='{{ route('football.index') }}?league_id=' + this.value"
                            class="bg-slate-900 border border-slate-700 text-white font-bold text-xs sm:text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all cursor-pointer">
                        @foreach($leagues as $l)
                            <option value="{{ $l['id'] }}" {{ $selectedLeagueId == $l['id'] ? 'selected' : '' }}>
                                {{ $l['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Season Select --}}
                @if(count($seasons) > 0)
                    <div class="flex items-center gap-2">
                        <label for="seasonSelect" class="text-xs font-bold uppercase tracking-wider text-slate-400">Musim:</label>
                        <select id="seasonSelect"
                                onchange="location.href='{{ route('football.index', ['league_id' => $selectedLeagueId]) }}&season_id=' + this.value + '&tab={{ $activeTab }}'"
                                class="bg-slate-900 border border-slate-700 text-white font-bold text-xs sm:text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all cursor-pointer">
                            @foreach($seasons as $s)
                                <option value="{{ $s['id'] }}" {{ $selectedSeasonId == $s['id'] ? 'selected' : '' }}>
                                    {{ $s['name'] }} {{ !empty($s['is_current']) ? '⭐️ (Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- SEASON STATS TICKER SUMMARY --}}
    @if($overview)
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-xl font-black border border-blue-500/20">
                    🛡️
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Klub Peserta</span>
                    <h3 class="text-xl font-extrabold text-white font-mono">{{ $overview['total_teams'] ?? 0 }}</h3>
                </div>
            </div>

            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xl font-black border border-emerald-500/20">
                    ⚽
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Pertandingan</span>
                    <h3 class="text-xl font-extrabold text-white font-mono">{{ $overview['total_fixtures'] ?? 0 }}</h3>
                </div>
            </div>

            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-xl font-black border border-purple-500/20">
                    🔄
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total Ronde</span>
                    <h3 class="text-xl font-extrabold text-white font-mono">{{ $overview['total_rounds'] ?? 0 }}</h3>
                </div>
            </div>

            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-xl font-black border border-amber-500/20">
                    👑
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Musim</span>
                    <h3 class="text-base font-extrabold text-white truncate max-w-[130px]">{{ $overview['season']['name'] ?? '-' }}</h3>
                </div>
            </div>
        </div>
    @endif

    {{-- MAIN TAB NAVIGATION --}}
    <div class="flex items-center gap-2 border-b border-slate-800 pb-1 overflow-x-auto">
        @php
            $tabs = [
                'fixtures' => ['label' => 'Jadwal & Hasil', 'icon' => '📅'],
                'standings' => ['label' => 'Klasemen Liga', 'icon' => '📊'],
                'topscorers' => ['label' => 'Top Skor', 'icon' => '👟'],
                'teams' => ['label' => 'Klub & Skuad', 'icon' => '🛡️'],
                'transfers' => ['label' => 'Bursa Transfer', 'icon' => '💸'],
            ];
        @endphp

        @foreach($tabs as $tabKey => $tabInfo)
            <a href="{{ route('football.index', ['league_id' => $selectedLeagueId, 'season_id' => $selectedSeasonId, 'tab' => $tabKey]) }}"
               class="px-5 py-3 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 transition-all whitespace-nowrap
                      {{ $activeTab === $tabKey 
                          ? 'bg-slate-800 text-emerald-400 border border-slate-700 shadow-md font-extrabold' 
                          : 'text-slate-400 hover:text-slate-200 hover:bg-slate-900' }}">
                <span>{{ $tabInfo['icon'] }}</span>
                <span>{{ $tabInfo['label'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- TAB 1: JADWAL & HASIL FIXTURES DENGAN SKOR TERKINI --}}
    @if($activeTab === 'fixtures')
        <div class="space-y-6">

            {{-- Round Select Dropdown --}}
            @if(count($rounds) > 0)
                <div class="bg-slate-900/90 border border-slate-800 p-4 rounded-2xl flex items-center gap-3 shadow-md max-w-sm">
                    <label for="roundSelect" class="text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">Pilih Ronde:</label>
                    <select id="roundSelect"
                            onchange="location.href='{{ route('football.index', ['league_id' => $selectedLeagueId, 'season_id' => $selectedSeasonId, 'tab' => 'fixtures']) }}' + (this.value ? '&round_id=' + this.value : '')"
                            class="bg-slate-950 border border-slate-700 text-white font-bold text-xs sm:text-sm rounded-xl px-3.5 py-2 w-full focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all cursor-pointer">
                        <option value="">Semua Ronde</option>
                        @foreach($rounds as $r)
                            <option value="{{ $r['id'] }}" {{ $selectedRoundId == $r['id'] ? 'selected' : '' }}>
                                Ronde {{ $r['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Fixtures Cards Grid --}}
            @if(count($fixtures) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($fixtures as $f)
                        @php
                            $stateCode = $f['state']['short_name'] ?? $f['state']['state'] ?? '';
                            $isLive = in_array($stateCode, ['LIVE', '1H', '2H', 'HT', 'ET']);
                            $isFinished = in_array($stateCode, ['FT', 'AET', 'FT_PEN']);
                            $hasScores = ($f['current_home_score'] !== null && $f['current_away_score'] !== null);

                            $homeName = $f['home_team']['name'] ?? explode(' vs ', $f['name'])[0] ?? 'Home';
                            $awayName = $f['away_team']['name'] ?? explode(' vs ', $f['name'])[1] ?? 'Away';
                            $homeLogo = $f['home_team']['image_path'] ?? null;
                            $awayLogo = $f['away_team']['image_path'] ?? null;
                        @endphp

                        <a href="{{ route('football.fixture', $f['id']) }}" 
                           class="group bg-slate-900/80 hover:bg-slate-850 border border-slate-800 hover:border-slate-700 rounded-2xl p-5 shadow-lg transition-all flex flex-col justify-between gap-4 relative overflow-hidden">
                            
                            {{-- Top Meta (Date & State Badge) --}}
                            <div class="flex items-center justify-between text-xs pb-3 border-b border-slate-800/80">
                                <div class="flex items-center gap-2">
                                    @if($stateCode)
                                        <span class="font-mono font-black text-[11px] px-2 py-0.5 rounded-md uppercase tracking-wider
                                            {{ $isLive ? 'bg-red-600 text-white animate-pulse' : '' }}
                                            {{ $isFinished ? 'bg-slate-800 text-slate-300 border border-slate-700' : '' }}
                                            {{ in_array($stateCode, ['NS', 'TBA']) ? 'bg-blue-950 text-blue-300 border border-blue-800' : '' }}
                                            {{ !in_array($stateCode, ['LIVE', '1H', '2H', 'HT', 'ET', 'FT', 'AET', 'FT_PEN', 'NS', 'TBA']) ? 'bg-slate-800 text-slate-300' : '' }}
                                        ">
                                            {{ $stateCode }}
                                        </span>
                                    @endif
                                    <span class="text-slate-400 font-medium">
                                        {{ $f['starting_at'] ? date('d M Y • H:i', strtotime($f['starting_at'])) : 'TBD' }}
                                    </span>
                                </div>

                                @if(!empty($f['venue']))
                                    <span class="text-slate-500 text-[11px] truncate max-w-[150px]">
                                        📍 {{ $f['venue']['name'] }}
                                    </span>
                                @endif
                            </div>

                            {{-- Teams & Center Current Score Box --}}
                            <div class="flex items-center justify-between gap-4 py-2">
                                {{-- Home Team --}}
                                <div class="flex-1 flex items-center gap-3">
                                    @if($homeLogo)
                                        <img src="{{ $homeLogo }}" alt="{{ $homeName }}" class="w-9 h-9 object-contain filter drop-shadow">
                                    @else
                                        <div class="w-9 h-9 rounded-xl bg-slate-800 flex items-center justify-center text-sm">🛡️</div>
                                    @endif
                                    <span class="font-bold text-sm text-slate-100 group-hover:text-emerald-400 transition-colors line-clamp-1">
                                        {{ $homeName }}
                                    </span>
                                </div>

                                {{-- Score / Kickoff Box --}}
                                <div class="flex-shrink-0 px-4 py-2 bg-slate-950 border border-slate-800 rounded-xl flex items-center justify-center font-mono shadow-inner min-w-[75px]">
                                    @if($hasScores)
                                        <span class="text-lg font-black text-white {{ $isLive ? 'text-emerald-400' : '' }}">
                                            {{ $f['current_home_score'] }} - {{ $f['current_away_score'] }}
                                        </span>
                                    @else
                                        <span class="text-xs font-bold text-slate-400">
                                            {{ $f['starting_at'] ? date('H:i', strtotime($f['starting_at'])) : 'VS' }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Away Team --}}
                                <div class="flex-1 flex items-center justify-end gap-3 text-right">
                                    <span class="font-bold text-sm text-slate-100 group-hover:text-emerald-400 transition-colors line-clamp-1">
                                        {{ $awayName }}
                                    </span>
                                    @if($awayLogo)
                                        <img src="{{ $awayLogo }}" alt="{{ $awayName }}" class="w-9 h-9 object-contain filter drop-shadow">
                                    @else
                                        <div class="w-9 h-9 rounded-xl bg-slate-800 flex items-center justify-center text-sm">🛡️</div>
                                    @endif
                                </div>
                            </div>

                            {{-- Bottom Action Link --}}
                            <div class="pt-2 text-right">
                                <span class="text-xs font-bold text-emerald-400 group-hover:underline inline-flex items-center gap-1">
                                    Match Center & Taktik &rarr;
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-12 text-center text-slate-400">
                    <div class="text-5xl mb-3">📅</div>
                    <p class="text-base font-bold text-slate-200">Belum ada pertandingan untuk musim ini.</p>
                    <p class="text-xs text-slate-500 mt-1">Jalankan scraper fixtures di backend untuk memuat jadwal pertandingan.</p>
                </div>
            @endif
        </div>
    @endif

    {{-- TAB 2: KLASEMEN LIGA --}}
    @if($activeTab === 'standings')
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-800">
                <div>
                    <h3 class="text-lg font-black text-white flex items-center gap-2">
                        📊 Klasemen Musim {{ $overview['season']['name'] ?? '' }}
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Peringkat klub berdasarkan perolehan poin terkini.</p>
                </div>
                <div class="hidden sm:flex items-center gap-4 text-xs font-bold">
                    <span class="flex items-center gap-1 text-blue-400"><span class="w-2 h-2 rounded-full bg-blue-500"></span> UCL (1-4)</span>
                    <span class="flex items-center gap-1 text-orange-400"><span class="w-2 h-2 rounded-full bg-orange-500"></span> UEL (5-6)</span>
                    <span class="flex items-center gap-1 text-red-400"><span class="w-2 h-2 rounded-full bg-red-500"></span> Degradasi</span>
                </div>
            </div>

            @if(count($standings) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm whitespace-nowrap">
                        <thead class="bg-slate-950/80 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-800 text-[11px]">
                            <tr>
                                <th class="py-3.5 px-3 text-center w-12">Pos</th>
                                <th class="py-3.5 px-4 min-w-[200px]">Klub</th>
                                <th class="py-3.5 px-3 text-center" title="Main (Played)">Main</th>
                                <th class="py-3.5 px-3 text-center text-emerald-400" title="Menang (Won)">M</th>
                                <th class="py-3.5 px-3 text-center text-amber-400" title="Seri (Draw)">S</th>
                                <th class="py-3.5 px-3 text-center text-red-400" title="Kalah (Lost)">K</th>
                                <th class="py-3.5 px-3 text-center" title="Gol Memasukkan - Gol Kemasukan">GM-GK</th>
                                <th class="py-3.5 px-3 text-center" title="Selisih Gol (Goal Difference)">SG</th>
                                <th class="py-3.5 px-4 text-center font-black text-emerald-400" title="Poin (Points)">Poin</th>
                                <th class="py-3.5 px-3 text-center" title="5 Laga Terakhir">Form</th>
                                <th class="py-3.5 px-3 text-center">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-medium text-slate-200">
                            @foreach($standings as $st)
                                @php
                                    $pos = $st['position'] ?? $loop->iteration;
                                    $team = $st['team'] ?? [];
                                    $isTop4 = ($pos <= 4);
                                    $isEuropa = ($pos == 5 || $pos == 6);
                                    $isBottom = ($pos >= 18);

                                    $played = $st['played'] ?? 0;
                                    $won = $st['won'] ?? 0;
                                    $draw = $st['draw'] ?? 0;
                                    $lost = $st['lost'] ?? 0;
                                    $gf = $st['goals_for'] ?? 0;
                                    $ga = $st['goals_against'] ?? 0;
                                    $gd = $st['goal_difference'] ?? 0;
                                @endphp
                                <tr class="hover:bg-slate-800/50 transition-colors">
                                    {{-- Position --}}
                                    <td class="py-3.5 px-3 text-center font-mono font-black">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-xs font-bold
                                            {{ $isTop4 ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : '' }}
                                            {{ $isEuropa ? 'bg-orange-500/20 text-orange-400 border border-orange-500/30' : '' }}
                                            {{ $isBottom ? 'bg-red-500/20 text-red-400 border border-red-500/30' : '' }}
                                            {{ !$isTop4 && !$isEuropa && !$isBottom ? 'text-slate-400' : '' }}
                                        ">
                                            {{ $pos }}
                                        </span>
                                    </td>

                                    {{-- Club Logo & Name --}}
                                    <td class="py-3.5 px-4 font-bold text-white">
                                        @if(!empty($team['id']))
                                            <a href="{{ route('football.team', $team['id']) }}?season_id={{ $selectedSeasonId }}" class="flex items-center gap-3 hover:text-emerald-400 transition-colors">
                                                @if(!empty($team['image_path']))
                                                    <img src="{{ $team['image_path'] }}" alt="{{ $team['name'] }}" class="w-6 h-6 object-contain filter drop-shadow">
                                                @else
                                                    <div class="w-6 h-6 rounded-md bg-slate-800 flex items-center justify-center text-xs">🛡️</div>
                                                @endif
                                                <span>{{ $team['name'] }}</span>
                                            </a>
                                        @else
                                            <span>Team #{{ $st['participant_id'] }}</span>
                                        @endif
                                    </td>

                                    {{-- Played --}}
                                    <td class="py-3.5 px-3 text-center font-mono font-bold text-slate-300">
                                        {{ $played }}
                                    </td>

                                    {{-- Won --}}
                                    <td class="py-3.5 px-3 text-center font-mono font-bold text-emerald-400">
                                        {{ $won }}
                                    </td>

                                    {{-- Draw --}}
                                    <td class="py-3.5 px-3 text-center font-mono font-bold text-amber-400">
                                        {{ $draw }}
                                    </td>

                                    {{-- Lost --}}
                                    <td class="py-3.5 px-3 text-center font-mono font-bold text-red-400">
                                        {{ $lost }}
                                    </td>

                                    {{-- Goals For - Goals Against --}}
                                    <td class="py-3.5 px-3 text-center font-mono text-slate-300">
                                        {{ $gf }}:{{ $ga }}
                                    </td>

                                    {{-- Goal Difference --}}
                                    <td class="py-3.5 px-3 text-center font-mono font-bold {{ $gd > 0 ? 'text-emerald-400' : ($gd < 0 ? 'text-red-400' : 'text-slate-400') }}">
                                        {{ $gd > 0 ? '+' . $gd : $gd }}
                                    </td>

                                    {{-- Points --}}
                                    <td class="py-3.5 px-4 text-center font-mono font-black text-base text-emerald-400 bg-slate-950/40">
                                        {{ $st['points'] ?? 0 }}
                                    </td>

                                    {{-- Recent Form (last 5: W/D/L pills) --}}
                                    <td class="py-3.5 px-3">
                                        @php $formArr = $st['form'] ?? []; @endphp
                                        @if(!empty($formArr))
                                            <div class="flex items-center justify-center gap-1">
                                                @foreach($formArr as $r)
                                                    @php
                                                        $r = strtoupper($r);
                                                        $cls = match($r) {
                                                            'W' => 'bg-emerald-500/90 text-slate-950',
                                                            'D' => 'bg-slate-600 text-white',
                                                            'L' => 'bg-red-500/90 text-white',
                                                            default => 'bg-slate-800 text-slate-400',
                                                        };
                                                        $label = match($r) { 'W' => 'Menang', 'D' => 'Seri', 'L' => 'Kalah', default => '' };
                                                    @endphp
                                                    <span title="{{ $label }}" class="flex h-5 w-5 items-center justify-center rounded text-[10px] font-black font-mono {{ $cls }}">{{ $r }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-slate-600 text-xs">—</span>
                                        @endif
                                    </td>

                                    {{-- Detail Squad Button --}}
                                    <td class="py-3.5 px-3 text-center">
                                        @if(!empty($team['id']))
                                            <a href="{{ route('football.team', $team['id']) }}?season_id={{ $selectedSeasonId }}" class="text-xs font-bold text-slate-400 hover:text-white px-2.5 py-1 bg-slate-800 rounded-lg border border-slate-700 hover:border-slate-600 transition-all">
                                                Skuad &rarr;
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-slate-400 text-center py-12">Belum ada data klasemen untuk musim ini.</p>
            @endif
        </div>
    @endif

    {{-- TAB 3: TOP SKOR & STATISTIK INDIVIDU (JOINED WITH TYPES) --}}
    @if($activeTab === 'topscorers')
        <div class="space-y-8">

            {{-- 4 Metric Categories Switcher (Goals, Assists, Yellow Cards, Red Cards) --}}
            @if(count($availableTypes) > 0)
                <div class="bg-slate-900/90 border border-slate-800 p-3 rounded-2xl flex items-center gap-2 overflow-x-auto">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider px-2">Kategori:</span>
                    @foreach($availableTypes as $tp)
                        @php
                            $isTypeSelected = ($selectedTypeId == $tp['id']);
                            $typeIcon = '⚽';
                            $typeNameLower = strtolower($tp['name'] ?? '');
                            if (str_contains($typeNameLower, 'assist')) {
                                $typeIcon = '👟';
                            } elseif (str_contains($typeNameLower, 'yellow') || str_contains($typeNameLower, 'kuning')) {
                                $typeIcon = '🟨';
                            } elseif (str_contains($typeNameLower, 'red') || str_contains($typeNameLower, 'merah')) {
                                $typeIcon = '🟥';
                            } elseif (str_contains($typeNameLower, 'card') || str_contains($typeNameLower, 'kartu')) {
                                $typeIcon = '🟨';
                            }
                        @endphp
                        <a href="{{ route('football.index', ['league_id' => $selectedLeagueId, 'season_id' => $selectedSeasonId, 'tab' => 'topscorers', 'type_id' => $tp['id']]) }}"
                           class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all
                                  {{ $isTypeSelected ? 'bg-emerald-500 text-slate-950 font-black shadow-md scale-105' : 'bg-slate-950 text-slate-300 hover:bg-slate-800 hover:text-white border border-slate-800' }}">
                            <span>{{ $typeIcon }}</span>
                            <span>{{ $tp['name'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            @php
                $activeTypeName = $topscorers[0]['type']['name'] ?? 'Total';
            @endphp

            {{-- Top 3 Podium Cards --}}
            @if(count($topscorers) >= 3)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
                    {{-- 2nd Place (Silver) --}}
                    @php $second = $topscorers[1] ?? null; @endphp
                    @if($second)
                        <div class="bg-gradient-to-b from-slate-800/80 to-slate-900 border border-slate-700 rounded-3xl p-6 text-center flex flex-col items-center justify-between shadow-xl relative order-2 md:order-1">
                            <div class="w-8 h-8 rounded-full bg-slate-300 text-slate-950 font-black text-sm flex items-center justify-center absolute -top-3 shadow-md">2</div>
                            <div class="my-4">
                                <a href="{{ route('football.player', $second['player']['id'] ?? $second['player_id']) }}" class="group block">
                                    @if(!empty($second['player']['image_path']))
                                        <img src="{{ $second['player']['image_path'] }}" alt="{{ $second['player']['name'] ?? 'Player' }}" class="w-20 h-20 rounded-full object-cover border-4 border-slate-400 shadow-xl mx-auto group-hover:scale-105 transition-transform">
                                    @else
                                        <div class="w-20 h-20 rounded-full bg-slate-700 flex items-center justify-center text-3xl mx-auto">👤</div>
                                    @endif
                                    <h4 class="font-extrabold text-base text-white mt-3 group-hover:text-emerald-400 transition-colors">{{ $second['player']['display_name'] ?? $second['player']['name'] ?? 'Pemain' }}</h4>
                                </a>
                                <p class="text-xs text-slate-400 font-semibold mt-0.5">{{ $second['team']['name'] ?? 'Klub' }}</p>
                            </div>
                            <div class="bg-slate-950 border border-slate-800 px-6 py-2 rounded-2xl font-mono font-black text-xl text-slate-200">
                                {{ $second['total'] ?? 0 }} <span class="text-xs font-sans text-slate-400 font-bold">{{ $second['type']['name'] ?? $activeTypeName }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- 1st Place (Gold) --}}
                    @php $first = $topscorers[0] ?? null; @endphp
                    @if($first)
                        <div class="bg-gradient-to-b from-amber-950/40 via-slate-900 to-slate-900 border-2 border-amber-500/50 rounded-3xl p-6 text-center flex flex-col items-center justify-between shadow-2xl relative order-1 md:order-2 md:-translate-y-4">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-amber-400 to-yellow-300 text-slate-950 font-black text-base flex items-center justify-center absolute -top-4 shadow-lg shadow-amber-500/30">👑 1</div>
                            <div class="my-4">
                                <a href="{{ route('football.player', $first['player']['id'] ?? $first['player_id']) }}" class="group block">
                                    @if(!empty($first['player']['image_path']))
                                        <img src="{{ $first['player']['image_path'] }}" alt="{{ $first['player']['name'] ?? 'Player' }}" class="w-24 h-24 rounded-full object-cover border-4 border-amber-400 shadow-2xl mx-auto group-hover:scale-105 transition-transform">
                                    @else
                                        <div class="w-24 h-24 rounded-full bg-amber-900/50 flex items-center justify-center text-4xl mx-auto">👤</div>
                                    @endif
                                    <h4 class="font-black text-lg text-white mt-3 group-hover:text-amber-300 transition-colors">{{ $first['player']['display_name'] ?? $first['player']['name'] ?? 'Pemain' }}</h4>
                                </a>
                                <p class="text-xs text-amber-300/80 font-bold mt-0.5">{{ $first['team']['name'] ?? 'Klub' }}</p>
                            </div>
                            <div class="bg-slate-950 border border-amber-500/40 px-8 py-2.5 rounded-2xl font-mono font-black text-2xl text-amber-400 shadow-inner">
                                {{ $first['total'] ?? 0 }} <span class="text-xs font-sans text-amber-300/80 font-bold">{{ $first['type']['name'] ?? $activeTypeName }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- 3rd Place (Bronze) --}}
                    @php $third = $topscorers[2] ?? null; @endphp
                    @if($third)
                        <div class="bg-gradient-to-b from-slate-800/80 to-slate-900 border border-slate-700 rounded-3xl p-6 text-center flex flex-col items-center justify-between shadow-xl relative order-3">
                            <div class="w-8 h-8 rounded-full bg-amber-700 text-white font-black text-sm flex items-center justify-center absolute -top-3 shadow-md">3</div>
                            <div class="my-4">
                                <a href="{{ route('football.player', $third['player']['id'] ?? $third['player_id']) }}" class="group block">
                                    @if(!empty($third['player']['image_path']))
                                        <img src="{{ $third['player']['image_path'] }}" alt="{{ $third['player']['name'] ?? 'Player' }}" class="w-20 h-20 rounded-full object-cover border-4 border-amber-700 shadow-xl mx-auto group-hover:scale-105 transition-transform">
                                    @else
                                        <div class="w-20 h-20 rounded-full bg-slate-700 flex items-center justify-center text-3xl mx-auto">👤</div>
                                    @endif
                                    <h4 class="font-extrabold text-base text-white mt-3 group-hover:text-emerald-400 transition-colors">{{ $third['player']['display_name'] ?? $third['player']['name'] ?? 'Pemain' }}</h4>
                                </a>
                                <p class="text-xs text-slate-400 font-semibold mt-0.5">{{ $third['team']['name'] ?? 'Klub' }}</p>
                            </div>
                            <div class="bg-slate-950 border border-slate-800 px-6 py-2 rounded-2xl font-mono font-black text-xl text-slate-200">
                                {{ $third['total'] ?? 0 }} <span class="text-xs font-sans text-slate-400 font-bold">{{ $third['type']['name'] ?? $activeTypeName }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Full Topscorers Table --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-black text-white">🏆 Peringkat: {{ $activeTypeName }}</h3>
                    <span class="text-xs text-slate-400 font-bold">{{ count($topscorers) }} Pemain</span>
                </div>
                @if(count($topscorers) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs sm:text-sm">
                            <thead class="bg-slate-950/80 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-800 text-[11px]">
                                <tr>
                                    <th class="py-3 px-3 text-center w-12">#</th>
                                    <th class="py-3 px-4">Pemain</th>
                                    <th class="py-3 px-4">Klub</th>
                                    <th class="py-3 px-3 text-center">Kategori</th>
                                    <th class="py-3 px-3 text-center">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60 font-medium text-slate-200">
                                @foreach($topscorers as $ts)
                                    @php
                                        $pl = $ts['player'] ?? [];
                                        $tm = $ts['team'] ?? [];
                                        $tp = $ts['type'] ?? [];
                                        $plId = $pl['id'] ?? $ts['player_id'];
                                    @endphp
                                    <tr class="hover:bg-slate-800/50 transition-colors">
                                        <td class="py-3 px-3 text-center font-mono font-bold text-slate-400">
                                            {{ $ts['position'] ?? $loop->iteration }}
                                        </td>
                                        <td class="py-3 px-4 font-bold text-white">
                                            <a href="{{ route('football.player', $plId) }}" class="flex items-center gap-3 hover:text-emerald-400 transition-colors">
                                                @if(!empty($pl['image_path']))
                                                    <img src="{{ $pl['image_path'] }}" alt="{{ $pl['name'] }}" class="w-8 h-8 rounded-full object-cover border border-slate-700">
                                                @else
                                                    <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-xs">👤</div>
                                                @endif
                                                <span>{{ $pl['display_name'] ?? $pl['name'] ?? 'Pemain #' . $ts['player_id'] }}</span>
                                            </a>
                                        </td>
                                        <td class="py-3 px-4 text-slate-300">
                                            @if(!empty($tm['name']))
                                                <span class="flex items-center gap-2">
                                                    @if(!empty($tm['image_path']))
                                                        <img src="{{ $tm['image_path'] }}" alt="{{ $tm['name'] }}" class="w-4 h-4 object-contain">
                                                    @endif
                                                    <span>{{ $tm['name'] }}</span>
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-center text-slate-400 text-xs">
                                            <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 font-semibold border border-slate-700">
                                                {{ $tp['name'] ?? 'Top Stat' }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-center font-mono font-black text-base text-emerald-400">
                                            {{ $ts['total'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-slate-400 text-center py-12">Belum ada data untuk kategori ini di musim terpilih.</p>
                @endif
            </div>
        </div>
    @endif

    {{-- TAB 4: KLUB & SQUAD --}}
    @if($activeTab === 'teams')
        <div class="space-y-6">
            <h3 class="text-base font-black text-white">🛡️ Klub Peserta Musim {{ $overview['season']['name'] ?? '' }}</h3>
            @if(count($teams) > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                    @foreach($teams as $t)
                        <a href="{{ route('football.team', $t['id']) }}?season_id={{ $selectedSeasonId }}"
                           class="group bg-slate-900/80 hover:bg-slate-850 border border-slate-800 hover:border-slate-700 rounded-3xl p-6 shadow-lg transition-all flex flex-col items-center text-center justify-between gap-4 hover:-translate-y-1">
                            <div class="w-20 h-20 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-center p-3 group-hover:scale-105 transition-transform shadow-inner">
                                @if(!empty($t['image_path']))
                                    <img src="{{ $t['image_path'] }}" alt="{{ $t['name'] }}" class="w-full h-full object-contain filter drop-shadow">
                                @else
                                    <span class="text-3xl">🛡️</span>
                                @endif
                            </div>
                            <div>
                                <h4 class="font-extrabold text-base text-white group-hover:text-emerald-400 transition-colors">{{ $t['name'] }}</h4>
                                @if(!empty($t['venue']))
                                    <p class="text-xs text-slate-400 mt-1">📍 {{ $t['venue']['name'] }}</p>
                                @endif
                            </div>
                            <span class="px-4 py-1.5 rounded-full bg-slate-800 text-xs font-bold text-slate-300 border border-slate-700">
                                {{ $t['squad_count'] ?? 0 }} Pemain Terdaftar
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-slate-400 text-center py-12">Belum ada data klub peserta untuk musim ini.</p>
            @endif
        </div>
    @endif

    {{-- TAB 5: BURSA TRANSFER --}}
    @if($activeTab === 'transfers')
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
            <h3 class="text-base font-black text-white flex items-center gap-2">
                💸 Riwayat Bursa Transfer Terkini
            </h3>
            @if(count($transfers) > 0)
                <div class="space-y-3">
                    @foreach($transfers as $tr)
                        @php
                            $pl = $tr['player'] ?? [];
                            $from = $tr['from_team'] ?? [];
                            $to = $tr['to_team'] ?? [];
                        @endphp
                        <div class="bg-slate-950/80 border border-slate-800/80 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                @if(!empty($pl['id']))
                                    <a href="{{ route('football.player', $pl['id']) }}" class="flex-shrink-0">
                                        @if(!empty($pl['image_path']))
                                            <img src="{{ $pl['image_path'] }}" alt="{{ $pl['name'] }}" class="w-10 h-10 rounded-full object-cover border border-slate-700">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-sm">👤</div>
                                        @endif
                                    </a>
                                @endif
                                <div>
                                    <a href="{{ route('football.player', $pl['id'] ?? $tr['player_id']) }}" class="font-extrabold text-sm text-white hover:text-emerald-400 transition-colors">
                                        {{ $pl['display_name'] ?? $pl['name'] ?? 'Pemain #' . $tr['player_id'] }}
                                    </a>
                                    <p class="text-xs text-slate-500">{{ $tr['date'] ? date('d F Y', strtotime($tr['date'])) : 'Resmi' }}</p>
                                </div>
                            </div>

                            {{-- Clubs Transfer Route --}}
                            <div class="flex items-center gap-3 text-xs font-bold">
                                <span class="text-slate-400 bg-slate-900 px-3 py-1.5 rounded-lg border border-slate-800">
                                    {{ $from['name'] ?? 'Klub Asal' }}
                                </span>
                                <span class="text-emerald-400 font-mono font-black">&rarr;</span>
                                <span class="text-emerald-300 bg-emerald-950/60 px-3 py-1.5 rounded-lg border border-emerald-800/50">
                                    {{ $to['name'] ?? 'Klub Tujuan' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-slate-400 text-center py-12">Belum ada data transfer untuk klub di musim ini.</p>
            @endif
        </div>
    @endif

</div>
@endsection
