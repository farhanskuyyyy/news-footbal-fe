@extends('layouts.app')

@section('title', __('football.portal.title'))

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
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> {{ __('football.portal.kicker') }}
                    </span>
                    <span class="text-xs text-slate-400">{{ __('football.portal.realtime_db') }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white flex items-center gap-3">
                    @if($selectedLeague && !empty($selectedLeague['image_path']))
                        <img src="{{ $selectedLeague['image_path'] }}" alt="{{ $selectedLeague['name'] }}" class="w-9 h-9 object-contain filter drop-shadow">
                    @endif
                    <span>{{ $selectedLeague['name'] ?? __('football.portal.choose_league') }}</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-xl">
                    {{ __('football.portal.subheading') }}
                </p>
            </div>

            {{-- Select Dropdown Inputs (League & Season) --}}
            <div class="flex flex-wrap items-center gap-3 bg-slate-950/90 p-3 rounded-2xl border border-slate-800 self-start md:self-auto shadow-xl">
                {{-- League Select --}}
                <div class="flex items-center gap-2">
                    <label for="leagueSelect" class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('football.portal.league_label') }}</label>
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
                        <label for="seasonSelect" class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('football.portal.season_label') }}</label>
                        <select id="seasonSelect"
                                onchange="location.href='{{ route('football.index', ['league_id' => $selectedLeagueId]) }}&season_id=' + this.value + '&tab={{ $activeTab }}'"
                                class="bg-slate-900 border border-slate-700 text-white font-bold text-xs sm:text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all cursor-pointer">
                            @foreach($seasons as $s)
                                <option value="{{ $s['id'] }}" {{ $selectedSeasonId == $s['id'] ? 'selected' : '' }}>
                                    {{ $s['name'] }} {{ !empty($s['is_current']) ? __('football.portal.current_season') : '' }}
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
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">{{ __('football.portal.stats.teams') }}</span>
                    <h3 class="text-xl font-extrabold text-white font-mono">{{ $overview['total_teams'] ?? 0 }}</h3>
                </div>
            </div>

            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xl font-black border border-emerald-500/20">
                    ⚽
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">{{ __('football.portal.stats.fixtures') }}</span>
                    <h3 class="text-xl font-extrabold text-white font-mono">{{ $overview['total_fixtures'] ?? 0 }}</h3>
                </div>
            </div>

            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-xl font-black border border-purple-500/20">
                    🔄
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">{{ __('football.portal.stats.rounds') }}</span>
                    <h3 class="text-xl font-extrabold text-white font-mono">{{ $overview['total_rounds'] ?? 0 }}</h3>
                </div>
            </div>

            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-xl font-black border border-amber-500/20">
                    👑
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">{{ __('football.portal.stats.season') }}</span>
                    <h3 class="text-base font-extrabold text-white truncate max-w-[130px]">{{ $overview['season']['name'] ?? '-' }}</h3>
                </div>
            </div>
        </div>
    @endif

    {{-- MAIN TAB NAVIGATION --}}
    <div class="flex items-center gap-2 border-b border-slate-800 pb-1 overflow-x-auto">
        @php
            $tabs = [
                'fixtures' => ['label' => __('football.portal.tabs.fixtures'), 'icon' => '📅'],
                'standings' => ['label' => __('football.portal.tabs.standings'), 'icon' => '📊'],
                'topscorers' => ['label' => __('football.portal.tabs.topscorers'), 'icon' => '👟'],
                'teams' => ['label' => __('football.portal.tabs.teams'), 'icon' => '🛡️'],
                'transfers' => ['label' => __('football.portal.tabs.transfers'), 'icon' => '💸'],
            ];

            // The bracket only means something for cups — seasons with
            // qualifying or knock-out stages. Domestic leagues have a single
            // group stage and would just repeat the standings tab.
            if (! empty($overview['has_bracket'])) {
                $tabs['bracket'] = ['label' => __('football.portal.tabs_bracket'), 'icon' => '🏆'];
            }
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

            @php
                // Base params every fixtures-tab link keeps; each control adds its own.
                $fxBase = ['league_id' => $selectedLeagueId, 'season_id' => $selectedSeasonId, 'tab' => 'fixtures'];
                $fxRoundBase = $selectedStatus ? $fxBase + ['status' => $selectedStatus] : $fxBase;
                $fxStatusBase = $selectedRoundId ? $fxBase + ['round_id' => $selectedRoundId] : $fxBase;
            @endphp

            {{-- Round Select Dropdown --}}
            @if(count($rounds) > 0)
                <div class="bg-slate-900/90 border border-slate-800 p-4 rounded-2xl flex items-center gap-3 shadow-md max-w-sm">
                    <label for="roundSelect" class="text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap">{{ __('football.portal.round_label') }}</label>
                    <select id="roundSelect"
                            onchange="location.href='{{ route('football.index', $fxRoundBase) }}' + (this.value ? '&round_id=' + this.value : '')"
                            class="bg-slate-950 border border-slate-700 text-white font-bold text-xs sm:text-sm rounded-xl px-3.5 py-2 w-full focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all cursor-pointer">
                        <option value="">{{ __('football.portal.all_rounds') }}</option>
                        @foreach($rounds as $r)
                            <option value="{{ $r['id'] }}" {{ $selectedRoundId == $r['id'] ? 'selected' : '' }}>
                                {{ __('football.portal.round', ['name' => $r['name']]) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Match status filter (FT / NS / …). Built from the states this
                 season actually has, so new ones appear without a code change. --}}
            @if(count($fixtureStatuses) > 0)
                <div class="bg-slate-900/90 border border-slate-800 p-3 rounded-2xl flex items-center gap-2 overflow-x-auto shadow-md">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider px-2 whitespace-nowrap">{{ __('football.portal.status_label') }}</span>

                    <a href="{{ route('football.index', $fxStatusBase) }}"
                       class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $selectedStatus === '' ? 'bg-emerald-500 text-slate-950 font-black shadow-md' : 'bg-slate-950 text-slate-300 hover:bg-slate-800 hover:text-white border border-slate-800' }}">
                        {{ __('football.portal.status_all') }}
                    </a>

                    @foreach($fixtureStatuses as $st)
                        @php
                            $code = strtoupper($st['short_name'] ?? '');
                            $isActive = strtoupper($selectedStatus) === $code;
                            // Friendly label for the states we know; otherwise the
                            // name Sportmonks gave us.
                            $statusKey = 'football.portal.status.'.strtolower($code);
                            $statusLabel = __($statusKey);
                            if ($statusLabel === $statusKey) {
                                $statusLabel = $st['name'] ?? $code;
                            }
                            $statusIcon = match (true) {
                                in_array($code, ['FT', 'AET', 'FTP']) => '✅',
                                in_array($code, ['NS', 'TBA']) => '🕒',
                                in_array($code, ['LIVE', 'INPLAY', '1H', '2H', 'HT', 'ET', 'PEN_LIVE', 'BREAK']) => '🔴',
                                default => '📌',
                            };
                        @endphp
                        <a href="{{ route('football.index', $fxStatusBase + ['status' => $code]) }}"
                           class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $isActive ? 'bg-emerald-500 text-slate-950 font-black shadow-md scale-105' : 'bg-slate-950 text-slate-300 hover:bg-slate-800 hover:text-white border border-slate-800' }}">
                            <span>{{ $statusIcon }}</span>
                            <span>{{ $statusLabel }}</span>
                            <span class="font-mono {{ $isActive ? 'text-slate-800' : 'text-slate-500' }}">{{ $st['count'] ?? 0 }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Fixtures Cards Grid --}}
            @if(count($fixtures) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($fixtures as $f)
                        @php
                            $stateCode = $f['state']['short_name'] ?? $f['state']['state'] ?? '';
                            $isLive = in_array($stateCode, ['LIVE', '1H', '2H', 'HT', 'ET']);
                            $isFinished = in_array($stateCode, ['FT', 'AET', 'FTP']);
                            $hasScores = ($f['current_home_score'] !== null && $f['current_away_score'] !== null);

                            $homeName = $f['home_team']['name'] ?? explode(' vs ', $f['name'])[0] ?? __('football.card.home');
                            $awayName = $f['away_team']['name'] ?? explode(' vs ', $f['name'])[1] ?? __('football.card.away');
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
                                            {{ !in_array($stateCode, ['LIVE', '1H', '2H', 'HT', 'ET', 'FT', 'AET', 'FTP', 'NS', 'TBA']) ? 'bg-slate-800 text-slate-300' : '' }}
                                        ">
                                            {{ $stateCode }}
                                        </span>
                                    @endif
                                    <span class="text-slate-400 font-medium">
                                        {{ $f['starting_at'] ? \Illuminate\Support\Carbon::parse($f['starting_at'], 'UTC')->setTimezone('Asia/Jakarta')->locale(app()->getLocale())->translatedFormat('d M Y • H:i') . ' WIB' : __('football.portal.tbd') }}
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
                                            {{ $f['starting_at'] ? \Illuminate\Support\Carbon::parse($f['starting_at'], 'UTC')->setTimezone('Asia/Jakarta')->format('H:i') : 'VS' }}
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
                                    {{ __('football.portal.match_center') }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-12 text-center text-slate-400">
                    <div class="text-5xl mb-3">📅</div>
                    @if($selectedStatus || $selectedRoundId)
                        {{-- Empty because of the active filter, not because the
                             season has no data — say so, and offer a way out. --}}
                        <p class="text-base font-bold text-slate-200">{{ __('football.portal.fixtures_empty_filtered') }}</p>
                        <a href="{{ route('football.index', $fxBase) }}" class="mt-3 inline-block text-xs font-bold text-emerald-400 hover:underline">{{ __('football.portal.clear_filter') }}</a>
                    @else
                        <p class="text-base font-bold text-slate-200">{{ __('football.portal.fixtures_empty') }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ __('football.portal.fixtures_empty_hint') }}</p>
                    @endif
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
                        📊 {{ __('football.portal.standings_heading', ['season' => $overview['season']['name'] ?? '']) }}
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ __('football.portal.standings_sub') }}</p>
                </div>
                <div class="hidden sm:flex items-center gap-4 text-xs font-bold">
                    <span class="flex items-center gap-1 text-blue-400"><span class="w-2 h-2 rounded-full bg-blue-500"></span> {{ __('football.portal.legend.ucl') }}</span>
                    <span class="flex items-center gap-1 text-orange-400"><span class="w-2 h-2 rounded-full bg-orange-500"></span> {{ __('football.portal.legend.uel') }}</span>
                    <span class="flex items-center gap-1 text-red-400"><span class="w-2 h-2 rounded-full bg-red-500"></span> {{ __('football.portal.legend.relegation') }}</span>
                </div>
            </div>

            @if(count($standings) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm whitespace-nowrap">
                        <thead class="bg-slate-950/80 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-800 text-[11px]">
                            <tr>
                                <th class="py-3.5 px-3 text-center w-12">{{ __('football.portal.table.pos') }}</th>
                                <th class="py-3.5 px-4 min-w-[200px]">{{ __('football.portal.table.club') }}</th>
                                <th class="py-3.5 px-3 text-center" title="{{ __('football.portal.table.played_title') }}">{{ __('football.portal.table.played') }}</th>
                                <th class="py-3.5 px-3 text-center text-emerald-400" title="{{ __('football.portal.table.won_title') }}">{{ __('football.portal.table.won') }}</th>
                                <th class="py-3.5 px-3 text-center text-amber-400" title="{{ __('football.portal.table.drawn_title') }}">{{ __('football.portal.table.drawn') }}</th>
                                <th class="py-3.5 px-3 text-center text-red-400" title="{{ __('football.portal.table.lost_title') }}">{{ __('football.portal.table.lost') }}</th>
                                <th class="py-3.5 px-3 text-center" title="{{ __('football.portal.table.goals_title') }}">{{ __('football.portal.table.goals') }}</th>
                                <th class="py-3.5 px-3 text-center" title="{{ __('football.portal.table.gd_title') }}">{{ __('football.portal.table.gd') }}</th>
                                <th class="py-3.5 px-4 text-center font-black text-emerald-400" title="{{ __('football.portal.table.points_title') }}">{{ __('football.portal.table.points') }}</th>
                                <th class="py-3.5 px-3 text-center" title="{{ __('football.portal.table.form_title') }}">{{ __('football.portal.table.form') }}</th>
                                <th class="py-3.5 px-3 text-center">{{ __('football.portal.table.detail') }}</th>
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
                                                        $label = match($r) { 'W' => __('football.portal.form.won'), 'D' => __('football.portal.form.drawn'), 'L' => __('football.portal.form.lost'), default => '' };
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
                                                {{ __('football.portal.squad_btn') }}
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-slate-400 text-center py-12">{{ __('football.portal.standings_empty') }}</p>
            @endif
        </div>
    @endif

    {{-- TAB 3: TOP SKOR & STATISTIK INDIVIDU (JOINED WITH TYPES) --}}
    @if($activeTab === 'topscorers')
        <div class="space-y-8">

            {{-- 4 Metric Categories Switcher (Goals, Assists, Yellow Cards, Red Cards) --}}
            @if(count($availableTypes) > 0)
                <div class="bg-slate-900/90 border border-slate-800 p-3 rounded-2xl flex items-center gap-2 overflow-x-auto">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider px-2">{{ __('football.portal.category_label') }}</span>
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
                $activeTypeName = $topscorers[0]['type']['name'] ?? __('football.portal.total');
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
                                    <h4 class="font-extrabold text-base text-white mt-3 group-hover:text-emerald-400 transition-colors">{{ $second['player']['display_name'] ?? $second['player']['name'] ?? __('football.portal.player') }}</h4>
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
                                    <h4 class="font-black text-lg text-white mt-3 group-hover:text-amber-300 transition-colors">{{ $first['player']['display_name'] ?? $first['player']['name'] ?? __('football.portal.player') }}</h4>
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
                                    <h4 class="font-extrabold text-base text-white mt-3 group-hover:text-emerald-400 transition-colors">{{ $third['player']['display_name'] ?? $third['player']['name'] ?? __('football.portal.player') }}</h4>
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
                    <h3 class="text-base font-black text-white">🏆 {{ __('football.portal.ranking', ['type' => $activeTypeName]) }}</h3>
                    <span class="text-xs text-slate-400 font-bold">{{ trans_choice('football.portal.players_count', count($topscorers), ['count' => count($topscorers)]) }}</span>
                </div>
                @if(count($topscorers) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs sm:text-sm">
                            <thead class="bg-slate-950/80 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-800 text-[11px]">
                                <tr>
                                    <th class="py-3 px-3 text-center w-12">#</th>
                                    <th class="py-3 px-4">{{ __('football.portal.th.player') }}</th>
                                    <th class="py-3 px-4">{{ __('football.portal.th.club') }}</th>
                                    <th class="py-3 px-3 text-center">{{ __('football.portal.th.category') }}</th>
                                    <th class="py-3 px-3 text-center">{{ __('football.portal.th.total') }}</th>
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
                                                <span>{{ $pl['display_name'] ?? $pl['name'] ?? __('football.portal.player_fallback', ['id' => $ts['player_id']]) }}</span>
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
                                                {{ $tp['name'] ?? __('football.portal.top_stat') }}
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
                    <p class="text-slate-400 text-center py-12">{{ __('football.portal.topscorers_empty') }}</p>
                @endif
            </div>
        </div>
    @endif

    {{-- TAB 4: KLUB & SQUAD --}}
    @if($activeTab === 'teams')
        <div class="space-y-6">
            <h3 class="text-base font-black text-white">🛡️ {{ __('football.portal.teams_heading', ['season' => $overview['season']['name'] ?? '']) }}</h3>
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
                                {{ trans_choice('football.portal.squad_registered', $t['squad_count'] ?? 0, ['count' => $t['squad_count'] ?? 0]) }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-slate-400 text-center py-12">{{ __('football.portal.teams_empty') }}</p>
            @endif
        </div>
    @endif

    {{-- TAB 5: BURSA TRANSFER --}}
    @if($activeTab === 'transfers')
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
            <h3 class="text-base font-black text-white flex items-center gap-2">
                💸 {{ __('football.portal.transfers_heading') }}
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
                                        {{ $pl['display_name'] ?? $pl['name'] ?? __('football.portal.player_fallback', ['id' => $tr['player_id']]) }}
                                    </a>
                                    <p class="text-xs text-slate-500">{{ $tr['date'] ? \Illuminate\Support\Carbon::parse($tr['date'])->locale(app()->getLocale())->translatedFormat('d F Y') : __('football.transfers.official') }}</p>
                                </div>
                            </div>

                            {{-- Clubs Transfer Route --}}
                            <div class="flex items-center gap-3 text-xs font-bold">
                                <span class="text-slate-400 bg-slate-900 px-3 py-1.5 rounded-lg border border-slate-800">
                                    {{ $from['name'] ?? __('football.transfers.from_club') }}
                                </span>
                                <span class="text-emerald-400 font-mono font-black">&rarr;</span>
                                <span class="text-emerald-300 bg-emerald-950/60 px-3 py-1.5 rounded-lg border border-emerald-800/50">
                                    {{ $to['name'] ?? __('football.transfers.to_club') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-slate-400 text-center py-12">{{ __('football.portal.transfers_empty') }}</p>
            @endif
        </div>
    @endif

    {{-- TAB 6: CUP BRACKET (STAGES) --}}
    @if($activeTab === 'bracket')
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-black text-white flex items-center gap-2">🏆 {{ __('football.portal.bracket.heading') }}</h3>
                <p class="text-xs text-slate-400 mt-0.5">{{ __('football.portal.bracket.sub') }}</p>
            </div>

            @php
                // Knock-out rounds are drawn as a tree; everything else stays a list.
                $koStages = array_values(array_filter($bracket, fn ($s) => ! empty($s['is_knockout']) && count($s['ties']) > 0));
                $restStages = array_values(array_filter($bracket, fn ($s) => empty($s['is_knockout']) || count($s['ties']) === 0));
            @endphp

            @if(count($koStages) > 0)
                {{-- KNOCK-OUT TREE — rounds left to right, scrolls sideways on small screens.
                     The backend already ordered each round so the ties feeding the same
                     later tie are adjacent, which is what makes the connectors line up. --}}
                <div class="rounded-3xl border border-slate-800 bg-slate-900/90 p-4 sm:p-6 shadow-xl">
                    <div class="overflow-x-auto pb-2">
                        <div class="flex min-w-max items-stretch">
                            @foreach($koStages as $ci => $stage)
                                @php
                                    $isLastColumn = $ci === count($koStages) - 1;
                                    // Group by the tie each one feeds: a group of two draws an
                                    // elbow, a group of one a straight line.
                                    $groups = [];
                                    foreach ($stage['ties'] as $tie) {
                                        $groups['n'.($tie['next_tie_id'] ?? 'x'.$tie['id'])][] = $tie;
                                    }
                                @endphp
                                <div class="flex flex-col">
                                    <div class="mb-3 px-1 text-center text-[10px] font-black uppercase tracking-widest text-slate-500 whitespace-nowrap">
                                        {{ $stage['name'] }}
                                    </div>
                                    <div class="flex flex-1 flex-col justify-around gap-4">
                                        @foreach($groups as $group)
                                            <div class="flex items-stretch">
                                                <div class="flex flex-1 flex-col justify-around gap-4">
                                                    @foreach($group as $tie)
                                                        @php $sides = $tie['sides'] ?? []; @endphp
                                                        <div class="w-[210px] shrink-0 rounded-xl border border-slate-800 bg-slate-950/80 p-2.5">
                                                            @foreach($sides as $side)
                                                                @php
                                                                    $team = $side['team'] ?? null;
                                                                    $isWinner = ($tie['winner_team_id'] ?? null) === ($side['team_id'] ?? null);
                                                                @endphp
                                                                <div class="flex items-center gap-2 py-0.5">
                                                                    @if(!empty($team['image_path']))
                                                                        <img src="{{ $team['image_path'] }}" alt="" class="h-4 w-4 shrink-0 object-contain">
                                                                    @else
                                                                        <span class="h-4 w-4 shrink-0"></span>
                                                                    @endif
                                                                    <a href="{{ !empty($team['id']) ? route('football.team', $team['id']) : '#' }}"
                                                                       class="flex-1 truncate text-[11px] {{ $isWinner ? 'font-black text-white' : 'font-semibold text-slate-500' }} hover:text-emerald-400 transition-colors">
                                                                        {{ $team['name'] ?? __('football.portal.bracket.tbd') }}
                                                                    </a>
                                                                    <span class="w-4 shrink-0 text-right font-mono text-[11px] {{ $isWinner ? 'font-black text-emerald-400' : 'font-bold text-slate-500' }}">
                                                                        {{ !empty($tie['played']) ? ($side['aggregate'] ?? 0) : '–' }}
                                                                    </span>
                                                                </div>
                                                            @endforeach

                                                            @php
                                                                $legScores = [];
                                                                foreach ($tie['legs'] as $leg) {
                                                                    $legScores[] = $leg['home_goals'] !== null
                                                                        ? $leg['home_goals'].'-'.$leg['away_goals']
                                                                        : ($leg['state']['short_name'] ?? 'NS');
                                                                }
                                                            @endphp
                                                            <div class="mt-1.5 border-t border-slate-800/80 pt-1 text-[9px] font-mono text-slate-600">
                                                                {{ implode(' · ', $legScores) }}
                                                                @if(($tie['decided_by'] ?? '') === 'level' && !empty($tie['result_info']))
                                                                    <span class="block text-amber-500/90 font-sans">{{ $tie['result_info'] }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                @unless($isLastColumn)
                                                    {{-- Two ties feeding one → elbow; a single tie → straight line. --}}
                                                    @if(count($group) > 1)
                                                        {{-- Inset ≈ half a card, so the elbow spans centre-to-centre. --}}
                                                        <div class="my-[34px] w-5 shrink-0 rounded-r-lg border-y border-r border-slate-700"></div>
                                                    @else
                                                        <div class="flex w-5 shrink-0 items-center"><span class="h-px w-full bg-slate-700"></span></div>
                                                    @endif
                                                @endunless
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                @unless($isLastColumn)
                                    {{-- Lead-in stub for the next round's cards. --}}
                                    <div class="flex flex-col justify-around">
                                        <div class="mb-3 h-[14px]"></div>
                                        <div class="flex flex-1 flex-col justify-around gap-4">
                                            @foreach($koStages[$ci + 1]['ties'] as $t)
                                                <div class="flex items-center"><span class="h-px w-4 bg-slate-700"></span></div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endunless
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @forelse($restStages as $stage)
                <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                    {{-- Stage header --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-800">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-slate-950 border border-slate-800 font-mono text-xs font-black text-emerald-400">
                                {{ $stage['sort_order'] ?? $loop->iteration }}
                            </span>
                            <div class="min-w-0">
                                <h4 class="truncate text-base font-black text-white">{{ $stage['name'] }}</h4>
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $stage['type_name'] ?: '—' }}</span>
                            </div>
                        </div>
                        {{-- Badge only when the source actually says so. A stage with
                             neither flag set gets none: the League Stage is flagged
                             neither finished nor current while its matches are being
                             played, so "not played yet" would simply be wrong. --}}
                        @if(!empty($stage['finished']) || !empty($stage['is_current']))
                            <span class="rounded-md px-2 py-0.5 text-[10px] font-black uppercase tracking-wider {{ !empty($stage['finished']) ? 'bg-slate-800 text-slate-300 border border-slate-700' : 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30' }}">
                                {{ !empty($stage['finished']) ? __('football.portal.bracket.finished') : __('football.portal.bracket.in_progress') }}
                            </span>
                        @endif
                    </div>

                    @if(($stage['kind'] ?? '') === 'bracket')
                        {{-- Ties: one card per matchup, both legs + aggregate --}}
                        @forelse($stage['ties'] as $tie)
                            @php $sides = $tie['sides'] ?? []; @endphp
                            <div class="rounded-2xl border border-slate-800 bg-slate-950/80 p-4">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    {{-- Both sides with their aggregate --}}
                                    <div class="flex-1 space-y-1.5 min-w-0">
                                        @foreach($sides as $side)
                                            @php
                                                $team = $side['team'] ?? null;
                                                $isWinner = ($tie['winner_team_id'] ?? null) === ($side['team_id'] ?? null);
                                            @endphp
                                            <div class="flex items-center gap-2.5">
                                                @if(!empty($team['image_path']))
                                                    <img src="{{ $team['image_path'] }}" alt="" class="h-6 w-6 object-contain shrink-0">
                                                @else
                                                    <div class="flex h-6 w-6 items-center justify-center rounded bg-slate-800 text-[10px] shrink-0">🛡️</div>
                                                @endif
                                                <a href="{{ !empty($team['id']) ? route('football.team', $team['id']) : '#' }}"
                                                   class="flex-1 truncate text-sm {{ $isWinner ? 'font-black text-white' : 'font-bold text-slate-400' }} hover:text-emerald-400 transition-colors">
                                                    {{ $team['name'] ?? __('football.portal.bracket.tbd') }}
                                                </a>
                                                @if($isWinner)
                                                    <span class="text-emerald-400 text-xs font-black">✓</span>
                                                @endif
                                                <span class="w-7 text-right font-mono text-sm {{ $isWinner ? 'font-black text-emerald-400' : 'font-bold text-slate-400' }}">
                                                    {{ !empty($tie['played']) ? ($side['aggregate'] ?? 0) : '–' }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Legs --}}
                                    <div class="sm:w-64 shrink-0 space-y-1 sm:border-l sm:border-slate-800 sm:pl-4">
                                        @foreach($tie['legs'] as $leg)
                                            <a href="{{ route('football.fixture', $leg['fixture_id']) }}"
                                               class="flex items-center justify-between gap-2 rounded-lg px-2 py-1 text-[11px] hover:bg-slate-900 transition-colors">
                                                <span class="font-bold uppercase tracking-wider text-slate-600 shrink-0">
                                                    {{ count($tie['legs']) > 1 ? __('football.portal.bracket.leg', ['number' => $loop->iteration]) : __('football.portal.bracket.single_leg') }}
                                                </span>
                                                <span class="truncate text-slate-500">{{ $leg['name'] }}</span>
                                                <span class="font-mono font-black text-slate-300 shrink-0">
                                                    {{ $leg['home_goals'] !== null ? $leg['home_goals'].'-'.$leg['away_goals'] : ($leg['state']['short_name'] ?? 'NS') }}
                                                </span>
                                            </a>
                                        @endforeach

                                        @if(($tie['decided_by'] ?? '') === 'level')
                                            {{-- Aggregate did not settle it; the shootout / extra-time
                                                 outcome exists only as the source's own wording. --}}
                                            <p class="px-2 pt-1 text-[10px] text-amber-400">
                                                {{ __('football.portal.bracket.level') }}@if(!empty($tie['result_info'])) — <span class="text-slate-400">{{ $tie['result_info'] }}</span>@endif
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="py-6 text-center text-xs text-slate-500">{{ __('football.portal.bracket.ties_empty') }}</p>
                        @endforelse
                    @else
                        {{-- Table stage: same shape as the standings tab --}}
                        @if(count($stage['standings']) > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs sm:text-sm whitespace-nowrap">
                                    <thead class="bg-slate-950/80 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-800 text-[11px]">
                                        <tr>
                                            <th class="py-3 px-3 text-center w-12">{{ __('football.portal.table.pos') }}</th>
                                            <th class="py-3 px-4 min-w-[180px]">{{ __('football.portal.table.club') }}</th>
                                            <th class="py-3 px-3 text-center" title="{{ __('football.portal.table.played_title') }}">{{ __('football.portal.table.played') }}</th>
                                            <th class="py-3 px-3 text-center text-emerald-400" title="{{ __('football.portal.table.won_title') }}">{{ __('football.portal.table.won') }}</th>
                                            <th class="py-3 px-3 text-center text-amber-400" title="{{ __('football.portal.table.drawn_title') }}">{{ __('football.portal.table.drawn') }}</th>
                                            <th class="py-3 px-3 text-center text-red-400" title="{{ __('football.portal.table.lost_title') }}">{{ __('football.portal.table.lost') }}</th>
                                            <th class="py-3 px-3 text-center" title="{{ __('football.portal.table.gd_title') }}">{{ __('football.portal.table.gd') }}</th>
                                            <th class="py-3 px-4 text-center font-black text-emerald-400" title="{{ __('football.portal.table.points_title') }}">{{ __('football.portal.table.points') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-800/60 font-medium text-slate-200">
                                        @foreach($stage['standings'] as $st)
                                            @php $team = $st['team'] ?? []; @endphp
                                            <tr class="hover:bg-slate-800/50 transition-colors">
                                                <td class="py-2.5 px-3 text-center font-mono font-bold text-slate-400">{{ $st['position'] ?? $loop->iteration }}</td>
                                                <td class="py-2.5 px-4">
                                                    <a href="{{ !empty($team['id']) ? route('football.team', $team['id']) : '#' }}" class="flex items-center gap-2.5 font-bold text-white hover:text-emerald-400 transition-colors">
                                                        @if(!empty($team['image_path']))<img src="{{ $team['image_path'] }}" alt="" class="h-5 w-5 object-contain">@endif
                                                        <span class="truncate">{{ $team['name'] ?? '-' }}</span>
                                                    </a>
                                                </td>
                                                <td class="py-2.5 px-3 text-center font-mono text-slate-400">{{ $st['played'] ?? 0 }}</td>
                                                <td class="py-2.5 px-3 text-center font-mono text-emerald-400">{{ $st['won'] ?? 0 }}</td>
                                                <td class="py-2.5 px-3 text-center font-mono text-amber-400">{{ $st['draw'] ?? 0 }}</td>
                                                <td class="py-2.5 px-3 text-center font-mono text-red-400">{{ $st['lost'] ?? 0 }}</td>
                                                <td class="py-2.5 px-3 text-center font-mono text-slate-300">{{ $st['goal_difference'] ?? 0 }}</td>
                                                <td class="py-2.5 px-4 text-center font-mono font-black text-base text-emerald-400 bg-slate-950/40">{{ $st['points'] ?? 0 }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="py-6 text-center text-xs text-slate-500">{{ __('football.portal.bracket.standings_empty') }}</p>
                        @endif
                    @endif
                </div>
            @empty
                <div class="bg-slate-900/60 border border-dashed border-slate-800 rounded-3xl p-12 text-center">
                    <div class="text-5xl mb-3">🏆</div>
                    <p class="text-base font-bold text-slate-200">{{ __('football.portal.bracket.empty') }}</p>
                </div>
            @endforelse
        </div>
    @endif

</div>
@endsection
