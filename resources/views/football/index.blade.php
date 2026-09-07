@extends('layouts.app')

@section('title', __('football.portal.title'))

@section('content')
<div class="space-y-8">

    {{-- HEADER & LEAGUE SELECTOR --}}
    <div class="relative overflow-hidden border border-line rounded-xl p-6 sm:p-8 relative overflow-hidden">
        {{-- Background Glow --}}

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="kicker inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold uppercase text-primary border border-line">
                        <span class="w-1.5 h-1.5 rounded-lg "></span> {{ __('football.portal.kicker') }}
                    </span>
                    <span class="text-xs text-body">{{ __('football.portal.realtime_db') }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight text-white flex items-center gap-3">
                    @if($selectedLeague && !empty($selectedLeague['image_path']))
                        <img src="{{ $selectedLeague['image_path'] }}" alt="{{ $selectedLeague['name'] }}" class="w-9 h-9 object-contain">
                    @endif
                    <span>{{ $selectedLeague['name'] ?? __('football.portal.choose_league') }}</span>
                </h1>
                <p class="text-xs sm:text-sm text-body mt-1 max-w-xl">
                    {{ __('football.portal.subheading') }}
                </p>
            </div>

            {{-- Select Dropdown Inputs (League & Season) --}}
            <div class="flex flex-wrap items-center gap-3 bg-ink p-3 rounded-xl border border-line self-start md:self-auto">
                {{-- League Select --}}
                <div class="flex items-center gap-2">
                    <label for="leagueSelect" class="text-xs font-bold uppercase tracking-wider text-body">{{ __('football.portal.league_label') }}</label>
                    <select id="leagueSelect"
                            onchange="location.href='{{ route('football.index') }}?league_id=' + this.value"
                            class="bg-surface border border-line text-white font-bold text-xs sm:text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-line focus:border-line transition-all cursor-pointer">
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
                        <label for="seasonSelect" class="text-xs font-bold uppercase tracking-wider text-body">{{ __('football.portal.season_label') }}</label>
                        <select id="seasonSelect"
                                onchange="location.href='{{ route('football.index', ['league_id' => $selectedLeagueId]) }}&season_id=' + this.value + '&tab={{ $activeTab }}'"
                                class="bg-surface border border-line text-white font-bold text-xs sm:text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-line focus:border-line transition-all cursor-pointer">
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
            <div class="bg-surface border border-line rounded-xl p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl text-steel flex items-center justify-center text-xl font-bold border border-line">
                    <x-icon name="shield" class="h-4 w-4" />
                </div>
                <div>
                    <span class="text-xs text-body font-bold uppercase tracking-wider">{{ __('football.portal.stats.teams') }}</span>
                    <h3 class="text-xl font-semibold text-white font-mono">{{ $overview['total_teams'] ?? 0 }}</h3>
                </div>
            </div>

            <div class="bg-surface border border-line rounded-xl p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl text-accent flex items-center justify-center text-xl font-bold border border-line">
                    <x-icon name="ball" class="h-4 w-4" />
                </div>
                <div>
                    <span class="text-xs text-body font-bold uppercase tracking-wider">{{ __('football.portal.stats.fixtures') }}</span>
                    <h3 class="text-xl font-semibold text-white font-mono">{{ $overview['total_fixtures'] ?? 0 }}</h3>
                </div>
            </div>

            <div class="bg-surface border border-line rounded-xl p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl text-steel flex items-center justify-center text-xl font-bold border border-purple-500/20">
                    <x-icon name="swap" class="h-4 w-4" />
                </div>
                <div>
                    <span class="text-xs text-body font-bold uppercase tracking-wider">{{ __('football.portal.stats.rounds') }}</span>
                    <h3 class="text-xl font-semibold text-white font-mono">{{ $overview['total_rounds'] ?? 0 }}</h3>
                </div>
            </div>

            <div class="bg-surface border border-line rounded-xl p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl text-gold flex items-center justify-center text-xl font-bold border border-line">
                    <x-icon name="crown" class="h-4 w-4" />
                </div>
                <div>
                    <span class="text-xs text-body font-bold uppercase tracking-wider">{{ __('football.portal.stats.season') }}</span>
                    <h3 class="text-base font-semibold text-white truncate max-w-[130px]">{{ $overview['season']['name'] ?? '-' }}</h3>
                </div>
            </div>
        </div>
    @endif

    {{-- MAIN TAB NAVIGATION --}}
    <div class="flex items-center gap-2 border-b border-line pb-1 overflow-x-auto">
        @php
            $tabs = [
                'fixtures' => ['label' => __('football.portal.tabs.fixtures'), 'icon' => \App\Support\Icon::svg('calendar', 'h-3.5 w-3.5')],
                'standings' => ['label' => __('football.portal.tabs.standings'), 'icon' => \App\Support\Icon::svg('chart', 'h-3.5 w-3.5')],
                'topscorers' => ['label' => __('football.portal.tabs.topscorers'), 'icon' => \App\Support\Icon::svg('boot', 'h-3.5 w-3.5')],
                'teams' => ['label' => __('football.portal.tabs.teams'), 'icon' => \App\Support\Icon::svg('shield', 'h-3.5 w-3.5')],
                'transfers' => ['label' => __('football.portal.tabs.transfers'), 'icon' => \App\Support\Icon::svg('transfer', 'h-3.5 w-3.5')],
            ];

            // The bracket only means something for cups — seasons with
            // qualifying or knock-out stages. Domestic leagues have a single
            // group stage and would just repeat the standings tab.
            if (! empty($overview['has_bracket'])) {
                $tabs['bracket'] = ['label' => __('football.portal.tabs_bracket'), 'icon' => \App\Support\Icon::svg('trophy', 'h-3.5 w-3.5')];
            }
        @endphp

        @foreach($tabs as $tabKey => $tabInfo)
            <a href="{{ route('football.index', ['league_id' => $selectedLeagueId, 'season_id' => $selectedSeasonId, 'tab' => $tabKey]) }}"
               class="px-5 py-3 rounded-xl text-xs sm:text-sm font-bold flex items-center gap-2 transition-all whitespace-nowrap
 {{ $activeTab === $tabKey 
                          ? 'bg-surface text-accent border border-line font-semibold' 
                          : 'text-body hover:text-white hover:bg-surface' }}">
                <span class="shrink-0">{!! $tabInfo['icon'] !!}</span>
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
                <div class="bg-surface border border-line p-4 rounded-xl flex items-center gap-3 max-w-sm">
                    <label for="roundSelect" class="text-xs font-bold text-body uppercase tracking-wider whitespace-nowrap">{{ __('football.portal.round_label') }}</label>
                    <select id="roundSelect"
                            onchange="location.href='{{ route('football.index', $fxRoundBase) }}' + (this.value ? '&round_id=' + this.value : '')"
                            class="bg-ink border border-line text-white font-bold text-xs sm:text-sm rounded-xl px-3.5 py-2 w-full focus:outline-none focus:ring-2 focus:ring-line focus:border-line transition-all cursor-pointer">
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
                <div class="bg-surface border border-line p-3 rounded-xl flex items-center gap-2 overflow-x-auto">
                    <span class="text-xs font-bold text-white0 uppercase tracking-wider px-2 whitespace-nowrap">{{ __('football.portal.status_label') }}</span>

                    <a href="{{ route('football.index', $fxStatusBase) }}"
                       class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $selectedStatus === '' ? 'bg-primary text-white font-bold' : 'bg-ink text-white hover:bg-surface hover:text-white border border-line' }}">
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
                                in_array($code, ['FT', 'AET', 'FTP']) => \App\Support\Icon::svg('check', 'h-3.5 w-3.5'),
                                in_array($code, ['NS', 'TBA']) => \App\Support\Icon::svg('clock', 'h-3.5 w-3.5'),
                                in_array($code, ['1st', '2nd', 'HT', 'BRK', 'et', 'ETB', '2et', 'PEN', 'PENB']) => \App\Support\Icon::svg('out', 'h-3.5 w-3.5'),
                                default => \App\Support\Icon::svg('pin', 'h-3.5 w-3.5'),
                            };
                        @endphp
                        <a href="{{ route('football.index', $fxStatusBase + ['status' => $code]) }}"
                           class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $isActive ? 'bg-primary text-white font-bold scale-105' : 'bg-ink text-white hover:bg-surface hover:text-white border border-line' }}">
                            <span class="shrink-0">{!! $statusIcon !!}</span>
                            <span>{{ $statusLabel }}</span>
                            <span class="font-mono {{ $isActive ? 'text-muted' : 'text-white0' }}">{{ $st['count'] ?? 0 }}</span>
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
                            // Live short_names as the `states` table stores them — '1H'/'2H'/'LIVE' are not real codes.
                            $isLive = in_array($stateCode, ['1st', '2nd', 'HT', 'BRK', 'et', 'ETB', '2et', 'PEN', 'PENB']);
                            $isFinished = in_array($stateCode, ['FT', 'AET', 'FTP']);
                            $hasScores = ($f['current_home_score'] !== null && $f['current_away_score'] !== null);

                            $homeName = $f['home_team']['name'] ?? explode(' vs ', $f['name'])[0] ?? __('football.card.home');
                            $awayName = $f['away_team']['name'] ?? explode(' vs ', $f['name'])[1] ?? __('football.card.away');
                            $homeLogo = $f['home_team']['image_path'] ?? null;
                            $awayLogo = $f['away_team']['image_path'] ?? null;
                        @endphp

                        <a href="{{ route('football.fixture', $f['id']) }}" 
                           class="group bg-surface hover:bg-surface border border-line hover:border-line rounded-xl p-5 transition-all flex flex-col justify-between gap-4 relative overflow-hidden">
                            
                            {{-- Top Meta (Date & State Badge) --}}
                            <div class="flex items-center justify-between text-xs pb-3 border-b border-line">
                                <div class="flex items-center gap-2">
                                    @if($stateCode)
                                        <span class="font-mono font-bold text-xs px-2 py-0.5 rounded-lg uppercase tracking-wider
 {{ $isLive ? 'text-white' : '' }}
                                            {{ $isFinished ? 'bg-surface text-white border border-line' : '' }}
                                            {{ in_array($stateCode, ['NS', 'TBA']) ? 'text-steel border border-line' : '' }}
                                            {{ !in_array($stateCode, ['1st', '2nd', 'HT', 'BRK', 'et', 'ETB', '2et', 'PEN', 'PENB', 'FT', 'AET', 'FTP', 'NS', 'TBA']) ? 'bg-surface text-white' : '' }}
                                        ">
                                            {{ $stateCode }}
                                        </span>
                                    @endif
                                    <span class="text-body font-medium">
                                        {{ $f['starting_at'] ? \Illuminate\Support\Carbon::parse($f['starting_at'], 'UTC')->setTimezone('Asia/Jakarta')->locale(app()->getLocale())->translatedFormat('d M Y • H:i') . ' WIB' : __('football.portal.tbd') }}
                                    </span>
                                </div>

                                @if(!empty($f['venue']))
                                    <span class="text-white0 text-xs truncate max-w-[150px]">
                                        <x-icon name="location" class="h-4 w-4" /> {{ $f['venue']['name'] }}
                                    </span>
                                @endif
                            </div>

                            {{-- Teams & Center Current Score Box --}}
                            <div class="flex items-center justify-between gap-4 py-2">
                                {{-- Home Team --}}
                                <div class="flex-1 flex items-center gap-3">
                                    @if($homeLogo)
                                        <img src="{{ $homeLogo }}" alt="{{ $homeName }}" class="w-9 h-9 object-contain">
                                    @else
                                        <div class="w-9 h-9 rounded-xl bg-surface flex items-center justify-center text-sm"><x-icon name="shield" class="h-4 w-4" /></div>
                                    @endif
                                    <span class="font-bold text-sm text-white group-hover:text-accent transition-colors line-clamp-1">
                                        {{ $homeName }}
                                    </span>
                                </div>

                                {{-- Score / Kickoff Box --}}
                                <div class="flex-shrink-0 px-4 py-2 bg-ink border border-line rounded-xl flex items-center justify-center font-mono min-w-[75px]">
                                    @if($hasScores)
                                        <span class="text-lg font-bold text-white {{ $isLive ? 'text-accent' : '' }}">
                                            {{ $f['current_home_score'] }} - {{ $f['current_away_score'] }}
                                        </span>
                                    @else
                                        <span class="text-xs font-bold text-body">
                                            {{ $f['starting_at'] ? \Illuminate\Support\Carbon::parse($f['starting_at'], 'UTC')->setTimezone('Asia/Jakarta')->format('H:i') : 'VS' }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Away Team --}}
                                <div class="flex-1 flex items-center justify-end gap-3 text-right">
                                    <span class="font-bold text-sm text-white group-hover:text-accent transition-colors line-clamp-1">
                                        {{ $awayName }}
                                    </span>
                                    @if($awayLogo)
                                        <img src="{{ $awayLogo }}" alt="{{ $awayName }}" class="w-9 h-9 object-contain">
                                    @else
                                        <div class="w-9 h-9 rounded-xl bg-surface flex items-center justify-center text-sm"><x-icon name="shield" class="h-4 w-4" /></div>
                                    @endif
                                </div>
                            </div>

                            {{-- Bottom Action Link --}}
                            <div class="pt-2 text-right">
                                <span class="text-xs font-bold text-accent group-hover:underline inline-flex items-center gap-1">
                                    {{ __('football.portal.match_center') }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="bg-surface border border-line rounded-xl p-12 text-center text-body">
                    <div class="text-5xl mb-3"><x-icon name="calendar" class="h-4 w-4" /></div>
                    @if($selectedStatus || $selectedRoundId)
                        {{-- Empty because of the active, not because the
                             season has no data — say so, and offer a way out. --}}
                        <p class="text-base font-bold text-white">{{ __('football.portal.fixtures_empty_filtered') }}</p>
                        <a href="{{ route('football.index', $fxBase) }}" class="mt-3 inline-block text-xs font-bold text-accent hover:underline">{{ __('football.portal.clear_') }}</a>
                    @else
                        <p class="text-base font-bold text-white">{{ __('football.portal.fixtures_empty') }}</p>
                        <p class="text-xs text-white0 mt-1">{{ __('football.portal.fixtures_empty_hint') }}</p>
                    @endif
                </div>
            @endif
        </div>
    @endif

    {{-- TAB 2: KLASEMEN LIGA --}}
    @if($activeTab === 'standings')
        <div class="bg-surface border border-line rounded-xl p-6">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-line">
                <div>
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <x-icon name="chart" class="h-4 w-4" /> {{ __('football.portal.standings_heading', ['season' => $overview['season']['name'] ?? '']) }}
                    </h3>
                    <p class="text-xs text-body mt-0.5">{{ __('football.portal.standings_sub') }}</p>
                </div>
                <div class="hidden sm:flex items-center gap-4 text-xs font-bold">
                    <span class="flex items-center gap-1 text-steel"><span class="w-2 h-2 rounded-full text-ink"></span> {{ __('football.portal.legend.ucl') }}</span>
                    <span class="flex items-center gap-1 text-gold"><span class="w-2 h-2 rounded-full text-ink"></span> {{ __('football.portal.legend.uel') }}</span>
                    <span class="flex items-center gap-1 text-accent"><span class="w-2 h-2 rounded-full "></span> {{ __('football.portal.legend.relegation') }}</span>
                </div>
            </div>

            @if(count($standings) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm whitespace-nowrap">
                        <thead class="bg-ink text-body font-bold uppercase tracking-wider border-b border-line text-xs">
                            <tr>
                                <th class="py-3.5 px-3 text-center w-12">{{ __('football.portal.table.pos') }}</th>
                                <th class="py-3.5 px-4 min-w-[200px]">{{ __('football.portal.table.club') }}</th>
                                <th class="py-3.5 px-3 text-center" title="{{ __('football.portal.table.played_title') }}">{{ __('football.portal.table.played') }}</th>
                                <th class="py-3.5 px-3 text-center text-accent" title="{{ __('football.portal.table.won_title') }}">{{ __('football.portal.table.won') }}</th>
                                <th class="py-3.5 px-3 text-center text-gold" title="{{ __('football.portal.table.drawn_title') }}">{{ __('football.portal.table.drawn') }}</th>
                                <th class="py-3.5 px-3 text-center text-accent" title="{{ __('football.portal.table.lost_title') }}">{{ __('football.portal.table.lost') }}</th>
                                <th class="py-3.5 px-3 text-center" title="{{ __('football.portal.table.goals_title') }}">{{ __('football.portal.table.goals') }}</th>
                                <th class="py-3.5 px-3 text-center" title="{{ __('football.portal.table.gd_title') }}">{{ __('football.portal.table.gd') }}</th>
                                <th class="py-3.5 px-4 text-center font-bold text-accent" title="{{ __('football.portal.table.points_title') }}">{{ __('football.portal.table.points') }}</th>
                                <th class="py-3.5 px-3 text-center" title="{{ __('football.portal.table.form_title') }}">{{ __('football.portal.table.form') }}</th>
                                <th class="py-3.5 px-3 text-center">{{ __('football.portal.table.detail') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line font-medium text-white">
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
                                <tr class="hover:bg-surface transition-colors">
                                    {{-- Position --}}
                                    <td class="py-3.5 px-3 text-center font-mono font-bold">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-xs font-bold
 {{ $isTop4 ? 'text-steel border border-line' : '' }}
                                            {{ $isEuropa ? 'text-gold border border-line' : '' }}
                                            {{ $isBottom ? 'text-accent border border-line' : '' }}
                                            {{ !$isTop4 && !$isEuropa && !$isBottom ? 'text-body' : '' }}
                                        ">
                                            {{ $pos }}
                                        </span>
                                    </td>

                                    {{-- Club Logo & Name --}}
                                    <td class="py-3.5 px-4 font-bold text-white">
                                        @if(!empty($team['id']))
                                            <a href="{{ route('football.team', $team['id']) }}?season_id={{ $selectedSeasonId }}" class="flex items-center gap-3 hover:text-accent transition-colors">
                                                @if(!empty($team['image_path']))
                                                    <img src="{{ $team['image_path'] }}" alt="{{ $team['name'] }}" class="w-6 h-6 object-contain">
                                                @else
                                                    <div class="w-6 h-6 rounded-lg bg-surface flex items-center justify-center text-xs"><x-icon name="shield" class="h-4 w-4" /></div>
                                                @endif
                                                <span>{{ $team['name'] }}</span>
                                            </a>
                                        @else
                                            <span>Team #{{ $st['participant_id'] }}</span>
                                        @endif
                                    </td>

                                    {{-- Played --}}
                                    <td class="py-3.5 px-3 text-center font-mono font-bold text-white">
                                        {{ $played }}
                                    </td>

                                    {{-- Won --}}
                                    <td class="py-3.5 px-3 text-center font-mono font-bold text-accent">
                                        {{ $won }}
                                    </td>

                                    {{-- Draw --}}
                                    <td class="py-3.5 px-3 text-center font-mono font-bold text-gold">
                                        {{ $draw }}
                                    </td>

                                    {{-- Lost --}}
                                    <td class="py-3.5 px-3 text-center font-mono font-bold text-accent">
                                        {{ $lost }}
                                    </td>

                                    {{-- Goals For - Goals Against --}}
                                    <td class="py-3.5 px-3 text-center font-mono text-white">
                                        {{ $gf }}:{{ $ga }}
                                    </td>

                                    {{-- Goal Difference --}}
                                    <td class="py-3.5 px-3 text-center font-mono font-bold {{ $gd > 0 ? 'text-accent' : ($gd < 0 ? 'text-accent' : 'text-body') }}">
                                        {{ $gd > 0 ? '+' . $gd : $gd }}
                                    </td>

                                    {{-- Points --}}
                                    <td class="py-3.5 px-4 text-center font-mono font-bold text-base text-accent bg-ink">
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
                                                            'W' => 'bg-primary text-white',
                                                            'D' => 'bg-line text-white',
                                                            'L' => 'bg-accent text-white',
                                                            default => 'bg-surface text-body',
                                                        };
                                                        $label = match($r) { 'W' => __('football.portal.form.won'), 'D' => __('football.portal.form.drawn'), 'L' => __('football.portal.form.lost'), default => '' };
                                                    @endphp
                                                    <span title="{{ $label }}" class="flex h-5 w-5 items-center justify-center rounded-lg text-xs font-bold font-mono {{ $cls }}">{{ $r }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted text-xs">—</span>
                                        @endif
                                    </td>

                                    {{-- Detail Squad Button --}}
                                    <td class="py-3.5 px-3 text-center">
                                        @if(!empty($team['id']))
                                            <a href="{{ route('football.team', $team['id']) }}?season_id={{ $selectedSeasonId }}" class="text-xs font-bold text-body hover:text-white px-2.5 py-1 bg-surface rounded-lg border border-line hover:border-line transition-all">
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
                <p class="text-body text-center py-12">{{ __('football.portal.standings_empty') }}</p>
            @endif
        </div>
    @endif

    {{-- TAB 3: TOP SKOR & STATISTIK INDIVIDU (JOINED WITH TYPES) --}}
    @if($activeTab === 'topscorers')
        <div class="space-y-8">

            {{-- 4 Metric Categories Switcher (Goals, Assists, Yellow Cards, Red Cards) --}}
            @if(count($availableTypes) > 0)
                <div class="bg-surface border border-line p-3 rounded-xl flex items-center gap-2 overflow-x-auto">
                    <span class="text-xs font-bold text-white0 uppercase tracking-wider px-2">{{ __('football.portal.category_label') }}</span>
                    @foreach($availableTypes as $tp)
                        @php
                            $isTypeSelected = ($selectedTypeId == $tp['id']);
                            $typeIcon = \App\Support\Icon::svg('ball', 'h-3.5 w-3.5');
                            $typeNameLower = strtolower($tp['name'] ?? '');
                            if (str_contains($typeNameLower, 'assist')) {
                                $typeIcon = \App\Support\Icon::svg('boot', 'h-3.5 w-3.5');
                            } elseif (str_contains($typeNameLower, 'yellow') || str_contains($typeNameLower, 'kuning')) {
                                $typeIcon = \App\Support\Icon::svg('card-yellow', 'h-3.5 w-3.5');
                            } elseif (str_contains($typeNameLower, 'red') || str_contains($typeNameLower, 'merah')) {
                                $typeIcon = \App\Support\Icon::svg('card-red', 'h-3.5 w-3.5');
                            } elseif (str_contains($typeNameLower, 'card') || str_contains($typeNameLower, 'kartu')) {
                                $typeIcon = \App\Support\Icon::svg('card-yellow', 'h-3.5 w-3.5');
                            }
                        @endphp
                        <a href="{{ route('football.index', ['league_id' => $selectedLeagueId, 'season_id' => $selectedSeasonId, 'tab' => 'topscorers', 'type_id' => $tp['id']]) }}"
                           class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all
 {{ $isTypeSelected ? 'bg-primary text-white font-bold scale-105' : 'bg-ink text-white hover:bg-surface hover:text-white border border-line' }}">
                            <span class="shrink-0">{!! $typeIcon !!}</span>
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
                        <div class="bg-surface border border-line rounded-xl p-6 text-center flex flex-col items-center justify-between relative order-2 md:order-1">
                            <div class="w-8 h-8 rounded-lg bg-line text-white font-bold text-sm flex items-center justify-center absolute -top-3">2</div>
                            <div class="my-4">
                                <a href="{{ route('football.player', $second['player']['id'] ?? $second['player_id']) }}" class="group block">
                                    @if(!empty($second['player']['image_path']))
                                        <img src="{{ $second['player']['image_path'] }}" alt="{{ $second['player']['name'] ?? 'Player' }}" class="w-20 h-20 rounded-lg object-cover border-4 border-line mx-auto group-">
                                    @else
                                        <div class="w-20 h-20 rounded-lg bg-surface flex items-center justify-center text-3xl mx-auto"><x-icon name="user" class="h-4 w-4" /></div>
                                    @endif
                                    <h4 class="font-semibold text-base text-white mt-3 group-hover:text-accent transition-colors">{{ $second['player']['display_name'] ?? $second['player']['name'] ?? __('football.portal.player') }}</h4>
                                </a>
                                <p class="text-xs text-body font-semibold mt-0.5">{{ $second['team']['name'] ?? 'Klub' }}</p>
                            </div>
                            <div class="bg-ink border border-line px-6 py-2 rounded-xl font-mono font-bold text-xl text-white">
                                {{ $second['total'] ?? 0 }} <span class="text-xs font-sans text-body font-bold">{{ $second['type']['name'] ?? $activeTypeName }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- 1st Place (Gold) --}}
                    @php $first = $topscorers[0] ?? null; @endphp
                    @if($first)
                        <div class="bg-surface border border-line rounded-xl p-6 text-center flex flex-col items-center justify-between relative order-1 md:order-2 md:-translate-y-4">
                            <div class="w-10 h-10 rounded-lg bg-surface text-muted font-bold text-base flex items-center justify-center absolute -top-4"><x-icon name="crown" class="h-4 w-4" /> 1</div>
                            <div class="my-4">
                                <a href="{{ route('football.player', $first['player']['id'] ?? $first['player_id']) }}" class="group block">
                                    @if(!empty($first['player']['image_path']))
                                        <img src="{{ $first['player']['image_path'] }}" alt="{{ $first['player']['name'] ?? 'Player' }}" class="w-24 h-24 rounded-lg object-cover border-4 border-line mx-auto group-">
                                    @else
                                        <div class="w-24 h-24 rounded-lg flex items-center justify-center text-4xl mx-auto"><x-icon name="user" class="h-4 w-4" /></div>
                                    @endif
                                    <h4 class="font-bold text-lg text-white mt-3 group-hover:text-gold transition-colors">{{ $first['player']['display_name'] ?? $first['player']['name'] ?? __('football.portal.player') }}</h4>
                                </a>
                                <p class="text-xs text-gold font-bold mt-0.5">{{ $first['team']['name'] ?? 'Klub' }}</p>
                            </div>
                            <div class="bg-ink border border-line px-8 py-2.5 rounded-xl font-mono font-bold text-2xl text-gold">
                                {{ $first['total'] ?? 0 }} <span class="text-xs font-sans text-gold font-bold">{{ $first['type']['name'] ?? $activeTypeName }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- 3rd Place (Bronze) --}}
                    @php $third = $topscorers[2] ?? null; @endphp
                    @if($third)
                        <div class="bg-surface border border-line rounded-xl p-6 text-center flex flex-col items-center justify-between relative order-3">
                            <div class="w-8 h-8 rounded-lg bg-gold text-white font-bold text-sm flex items-center justify-center absolute -top-3">3</div>
                            <div class="my-4">
                                <a href="{{ route('football.player', $third['player']['id'] ?? $third['player_id']) }}" class="group block">
                                    @if(!empty($third['player']['image_path']))
                                        <img src="{{ $third['player']['image_path'] }}" alt="{{ $third['player']['name'] ?? 'Player' }}" class="w-20 h-20 rounded-lg object-cover border-4 border-line mx-auto group-">
                                    @else
                                        <div class="w-20 h-20 rounded-lg bg-surface flex items-center justify-center text-3xl mx-auto"><x-icon name="user" class="h-4 w-4" /></div>
                                    @endif
                                    <h4 class="font-semibold text-base text-white mt-3 group-hover:text-accent transition-colors">{{ $third['player']['display_name'] ?? $third['player']['name'] ?? __('football.portal.player') }}</h4>
                                </a>
                                <p class="text-xs text-body font-semibold mt-0.5">{{ $third['team']['name'] ?? 'Klub' }}</p>
                            </div>
                            <div class="bg-ink border border-line px-6 py-2 rounded-xl font-mono font-bold text-xl text-white">
                                {{ $third['total'] ?? 0 }} <span class="text-xs font-sans text-body font-bold">{{ $third['type']['name'] ?? $activeTypeName }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Full Topscorers Table --}}
            <div class="bg-surface border border-line rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-white"><x-icon name="trophy" class="h-4 w-4" /> {{ __('football.portal.ranking', ['type' => $activeTypeName]) }}</h3>
                    <span class="text-xs text-body font-bold">{{ trans_choice('football.portal.players_count', count($topscorers), ['count' => count($topscorers)]) }}</span>
                </div>
                @if(count($topscorers) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs sm:text-sm">
                            <thead class="bg-ink text-body font-bold uppercase tracking-wider border-b border-line text-xs">
                                <tr>
                                    <th class="py-3 px-3 text-center w-12">#</th>
                                    <th class="py-3 px-4">{{ __('football.portal.th.player') }}</th>
                                    <th class="py-3 px-4">{{ __('football.portal.th.club') }}</th>
                                    <th class="py-3 px-3 text-center">{{ __('football.portal.th.category') }}</th>
                                    <th class="py-3 px-3 text-center">{{ __('football.portal.th.total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line font-medium text-white">
                                @foreach($topscorers as $ts)
                                    @php
                                        $pl = $ts['player'] ?? [];
                                        $tm = $ts['team'] ?? [];
                                        $tp = $ts['type'] ?? [];
                                        $plId = $pl['id'] ?? $ts['player_id'];
                                    @endphp
                                    <tr class="hover:bg-surface transition-colors">
                                        <td class="py-3 px-3 text-center font-mono font-bold text-body">
                                            {{ $ts['position'] ?? $loop->iteration }}
                                        </td>
                                        <td class="py-3 px-4 font-bold text-white">
                                            <a href="{{ route('football.player', $plId) }}" class="flex items-center gap-3 hover:text-accent transition-colors">
                                                @if(!empty($pl['image_path']))
                                                    <img src="{{ $pl['image_path'] }}" alt="{{ $pl['name'] }}" class="w-8 h-8 rounded-lg object-cover border border-line">
                                                @else
                                                    <div class="w-8 h-8 rounded-lg bg-surface flex items-center justify-center text-xs"><x-icon name="user" class="h-4 w-4" /></div>
                                                @endif
                                                <span>{{ $pl['display_name'] ?? $pl['name'] ?? __('football.portal.player_fallback', ['id' => $ts['player_id']]) }}</span>
                                            </a>
                                        </td>
                                        <td class="py-3 px-4 text-white">
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
                                        <td class="py-3 px-3 text-center text-body text-xs">
                                            <span class="px-2 py-0.5 rounded-lg bg-surface text-white font-semibold border border-line">
                                                {{ $tp['name'] ?? __('football.portal.top_stat') }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-center font-mono font-bold text-base text-accent">
                                            {{ $ts['total'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-body text-center py-12">{{ __('football.portal.topscorers_empty') }}</p>
                @endif
            </div>
        </div>
    @endif

    {{-- TAB 4: KLUB & SQUAD --}}
    @if($activeTab === 'teams')
        <div class="space-y-6">
            <h3 class="text-base font-bold text-white"><x-icon name="shield" class="h-4 w-4" /> {{ __('football.portal.teams_heading', ['season' => $overview['season']['name'] ?? '']) }}</h3>
            @if(count($teams) > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                    @foreach($teams as $t)
                        <a href="{{ route('football.team', $t['id']) }}?season_id={{ $selectedSeasonId }}"
                           class="group bg-surface hover:bg-surface border border-line hover:border-line rounded-xl p-6 transition-all flex flex-col items-center text-center justify-between gap-4">
                            <div class="w-20 h-20 rounded-xl bg-ink border border-line flex items-center justify-center p-3 group-">
                                @if(!empty($t['image_path']))
                                    <img src="{{ $t['image_path'] }}" alt="{{ $t['name'] }}" class="w-full h-full object-contain">
                                @else
                                    <span class="text-3xl"><x-icon name="shield" class="h-4 w-4" /></span>
                                @endif
                            </div>
                            <div>
                                <h4 class="font-semibold text-base text-white group-hover:text-accent transition-colors">{{ $t['name'] }}</h4>
                                @if(!empty($t['venue']))
                                    <p class="text-xs text-body mt-1"><x-icon name="location" class="h-4 w-4" /> {{ $t['venue']['name'] }}</p>
                                @endif
                            </div>
                            <span class="px-4 py-1.5 rounded-lg bg-surface text-xs font-bold text-white border border-line">
                                {{ trans_choice('football.portal.squad_registered', $t['squad_count'] ?? 0, ['count' => $t['squad_count'] ?? 0]) }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-body text-center py-12">{{ __('football.portal.teams_empty') }}</p>
            @endif
        </div>
    @endif

    {{-- TAB 5: BURSA TRANSFER --}}
    @if($activeTab === 'transfers')
        <div class="bg-surface border border-line rounded-xl p-6 space-y-6">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <x-icon name="transfer" class="h-4 w-4" /> {{ __('football.portal.transfers_heading') }}
            </h3>
            @if(count($transfers) > 0)
                <div class="space-y-3">
                    @foreach($transfers as $tr)
                        @php
                            $pl = $tr['player'] ?? [];
                            $from = $tr['from_team'] ?? [];
                            $to = $tr['to_team'] ?? [];
                        @endphp
                        <div class="bg-ink border border-line rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                @if(!empty($pl['id']))
                                    <a href="{{ route('football.player', $pl['id']) }}" class="flex-shrink-0">
                                        @if(!empty($pl['image_path']))
                                            <img src="{{ $pl['image_path'] }}" alt="{{ $pl['name'] }}" class="w-10 h-10 rounded-lg object-cover border border-line">
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-surface flex items-center justify-center text-sm"><x-icon name="user" class="h-4 w-4" /></div>
                                        @endif
                                    </a>
                                @endif
                                <div>
                                    <a href="{{ route('football.player', $pl['id'] ?? $tr['player_id']) }}" class="font-semibold text-sm text-white hover:text-accent transition-colors">
                                        {{ $pl['display_name'] ?? $pl['name'] ?? __('football.portal.player_fallback', ['id' => $tr['player_id']]) }}
                                    </a>
                                    <p class="text-xs text-white0">{{ $tr['date'] ? \Illuminate\Support\Carbon::parse($tr['date'])->locale(app()->getLocale())->translatedFormat('d F Y') : __('football.transfers.official') }}</p>
                                </div>
                            </div>

                            {{-- Clubs Transfer Route --}}
                            <div class="flex items-center gap-3 text-xs font-bold">
                                <span class="text-body bg-surface px-3 py-1.5 rounded-lg border border-line">
                                    {{ $from['name'] ?? __('football.transfers.from_club') }}
                                </span>
                                <span class="text-accent font-mono font-bold">&rarr;</span>
                                <span class="text-accent px-3 py-1.5 rounded-lg border border-line">
                                    {{ $to['name'] ?? __('football.transfers.to_club') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-body text-center py-12">{{ __('football.portal.transfers_empty') }}</p>
            @endif
        </div>
    @endif

    {{-- TAB 6: CUP BRACKET (STAGES) --}}
    @if($activeTab === 'bracket')
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-bold text-white flex items-center gap-2"><x-icon name="trophy" class="h-4 w-4" /> {{ __('football.portal.bracket.heading') }}</h3>
                <p class="text-xs text-body mt-0.5">{{ __('football.portal.bracket.sub') }}</p>
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
                <div class="rounded-xl border border-line bg-surface p-4 sm:p-6">
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
                                    <div class="mb-3 px-1 text-center text-xs font-bold uppercase tracking-widest text-white0 whitespace-nowrap">
                                        {{ $stage['name'] }}
                                    </div>
                                    <div class="flex flex-1 flex-col justify-around gap-4">
                                        @foreach($groups as $group)
                                            <div class="flex items-stretch">
                                                <div class="flex flex-1 flex-col justify-around gap-4">
                                                    @foreach($group as $tie)
                                                        @php $sides = $tie['sides'] ?? []; @endphp
                                                        <div class="w-[210px] shrink-0 rounded-xl border border-line bg-ink p-2.5">
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
                                                                       class="flex-1 truncate text-xs {{ $isWinner ? 'font-bold text-white' : 'font-semibold text-white0' }} hover:text-accent transition-colors">
                                                                        {{ $team['name'] ?? __('football.portal.bracket.tbd') }}
                                                                    </a>
                                                                    <span class="w-4 shrink-0 text-right font-mono text-xs {{ $isWinner ? 'font-bold text-accent' : 'font-bold text-white0' }}">
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
                                                            <div class="mt-1.5 border-t border-line pt-1 text-xs font-mono text-muted">
                                                                {{ implode(' · ', $legScores) }}
                                                                @if(($tie['decided_by'] ?? '') === 'level' && !empty($tie['result_info']))
                                                                    <span class="block text-gold font-sans">{{ $tie['result_info'] }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                @unless($isLastColumn)
                                                    {{-- Two ties feeding one <x-icon name="arrow-right" class="h-4 w-4" /> elbow; a single tie <x-icon name="arrow-right" class="h-4 w-4" /> straight line. --}}
                                                    @if(count($group) > 1)
                                                        {{-- Inset ≈ half a card, so the elbow spans centre-to-centre. --}}
                                                        <div class="my-[34px] w-5 shrink-0 rounded-r-lg border-y border-r border-line"></div>
                                                    @else
                                                        <div class="flex w-5 shrink-0 items-center"><span class="h-px w-full bg-surface"></span></div>
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
                                                <div class="flex items-center"><span class="h-px w-4 bg-surface"></span></div>
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
                <div class="bg-surface border border-line rounded-xl p-6 space-y-4">
                    {{-- Stage header --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-line">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-ink border border-line font-mono text-xs font-bold text-accent">
                                {{ $stage['sort_order'] ?? $loop->iteration }}
                            </span>
                            <div class="min-w-0">
                                <h4 class="truncate text-base font-bold text-white">{{ $stage['name'] }}</h4>
                                <span class="text-xs font-bold uppercase tracking-wider text-white0">{{ $stage['type_name'] ?: '—' }}</span>
                            </div>
                        </div>
                        {{-- Badge only when the source actually says so. A stage with
                             neither flag set gets none: the League Stage is flagged
                             neither finished nor current while its matches are being
                             played, so "not played yet" would simply be wrong. --}}
                        @if(!empty($stage['finished']) || !empty($stage['is_current']))
                            <span class="rounded-lg px-2 py-0.5 text-xs font-bold uppercase tracking-wider {{ !empty($stage['finished']) ? 'bg-surface text-white border border-line' : 'text-accent border border-line' }}">
                                {{ !empty($stage['finished']) ? __('football.portal.bracket.finished') : __('football.portal.bracket.in_progress') }}
                            </span>
                        @endif
                    </div>

                    @if(($stage['kind'] ?? '') === 'bracket')
                        {{-- Ties: one card per matchup, both legs + aggregate --}}
                        @forelse($stage['ties'] as $tie)
                            @php $sides = $tie['sides'] ?? []; @endphp
                            <div class="rounded-xl border border-line bg-ink p-4">
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
                                                    <div class="flex h-6 w-6 items-center justify-center rounded-lg bg-surface text-xs shrink-0"><x-icon name="shield" class="h-4 w-4" /></div>
                                                @endif
                                                <a href="{{ !empty($team['id']) ? route('football.team', $team['id']) : '#' }}"
                                                   class="flex-1 truncate text-sm {{ $isWinner ? 'font-bold text-white' : 'font-bold text-body' }} hover:text-accent transition-colors">
                                                    {{ $team['name'] ?? __('football.portal.bracket.tbd') }}
                                                </a>
                                                @if($isWinner)
                                                    <span class="text-accent text-xs font-bold"><x-icon name="check" class="h-4 w-4" /></span>
                                                @endif
                                                <span class="w-7 text-right font-mono text-sm {{ $isWinner ? 'font-bold text-accent' : 'font-bold text-body' }}">
                                                    {{ !empty($tie['played']) ? ($side['aggregate'] ?? 0) : '–' }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Legs --}}
                                    <div class="sm:w-64 shrink-0 space-y-1 sm:border-l sm:border-line sm:pl-4">
                                        @foreach($tie['legs'] as $leg)
                                            <a href="{{ route('football.fixture', $leg['fixture_id']) }}"
                                               class="flex items-center justify-between gap-2 rounded-lg px-2 py-1 text-xs hover:bg-surface transition-colors">
                                                <span class="font-bold uppercase tracking-wider text-muted shrink-0">
                                                    {{ count($tie['legs']) > 1 ? __('football.portal.bracket.leg', ['number' => $loop->iteration]) : __('football.portal.bracket.single_leg') }}
                                                </span>
                                                <span class="truncate text-white0">{{ $leg['name'] }}</span>
                                                <span class="font-mono font-bold text-white shrink-0">
                                                    {{ $leg['home_goals'] !== null ? $leg['home_goals'].'-'.$leg['away_goals'] : ($leg['state']['short_name'] ?? 'NS') }}
                                                </span>
                                            </a>
                                        @endforeach

                                        @if(($tie['decided_by'] ?? '') === 'level')
                                            {{-- Aggregate did not settle it; the shootout / extra-time
                                                 outcome exists only as the source's own wording. --}}
                                            <p class="px-2 pt-1 text-xs text-gold">
                                                {{ __('football.portal.bracket.level') }}@if(!empty($tie['result_info'])) — <span class="text-body">{{ $tie['result_info'] }}</span>@endif
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="py-6 text-center text-xs text-white0">{{ __('football.portal.bracket.ties_empty') }}</p>
                        @endforelse
                    @else
                        {{-- Table stage: same shape as the standings tab --}}
                        @if(count($stage['standings']) > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs sm:text-sm whitespace-nowrap">
                                    <thead class="bg-ink text-body font-bold uppercase tracking-wider border-b border-line text-xs">
                                        <tr>
                                            <th class="py-3 px-3 text-center w-12">{{ __('football.portal.table.pos') }}</th>
                                            <th class="py-3 px-4 min-w-[180px]">{{ __('football.portal.table.club') }}</th>
                                            <th class="py-3 px-3 text-center" title="{{ __('football.portal.table.played_title') }}">{{ __('football.portal.table.played') }}</th>
                                            <th class="py-3 px-3 text-center text-accent" title="{{ __('football.portal.table.won_title') }}">{{ __('football.portal.table.won') }}</th>
                                            <th class="py-3 px-3 text-center text-gold" title="{{ __('football.portal.table.drawn_title') }}">{{ __('football.portal.table.drawn') }}</th>
                                            <th class="py-3 px-3 text-center text-accent" title="{{ __('football.portal.table.lost_title') }}">{{ __('football.portal.table.lost') }}</th>
                                            <th class="py-3 px-3 text-center" title="{{ __('football.portal.table.gd_title') }}">{{ __('football.portal.table.gd') }}</th>
                                            <th class="py-3 px-4 text-center font-bold text-accent" title="{{ __('football.portal.table.points_title') }}">{{ __('football.portal.table.points') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-line font-medium text-white">
                                        @foreach($stage['standings'] as $st)
                                            @php $team = $st['team'] ?? []; @endphp
                                            <tr class="hover:bg-surface transition-colors">
                                                <td class="py-2.5 px-3 text-center font-mono font-bold text-body">{{ $st['position'] ?? $loop->iteration }}</td>
                                                <td class="py-2.5 px-4">
                                                    <a href="{{ !empty($team['id']) ? route('football.team', $team['id']) : '#' }}" class="flex items-center gap-2.5 font-bold text-white hover:text-accent transition-colors">
                                                        @if(!empty($team['image_path']))<img src="{{ $team['image_path'] }}" alt="" class="h-5 w-5 object-contain">@endif
                                                        <span class="truncate">{{ $team['name'] ?? '-' }}</span>
                                                    </a>
                                                </td>
                                                <td class="py-2.5 px-3 text-center font-mono text-body">{{ $st['played'] ?? 0 }}</td>
                                                <td class="py-2.5 px-3 text-center font-mono text-accent">{{ $st['won'] ?? 0 }}</td>
                                                <td class="py-2.5 px-3 text-center font-mono text-gold">{{ $st['draw'] ?? 0 }}</td>
                                                <td class="py-2.5 px-3 text-center font-mono text-accent">{{ $st['lost'] ?? 0 }}</td>
                                                <td class="py-2.5 px-3 text-center font-mono text-white">{{ $st['goal_difference'] ?? 0 }}</td>
                                                <td class="py-2.5 px-4 text-center font-mono font-bold text-base text-accent bg-ink">{{ $st['points'] ?? 0 }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="py-6 text-center text-xs text-white0">{{ __('football.portal.bracket.standings_empty') }}</p>
                        @endif
                    @endif
                </div>
            @empty
                <div class="bg-surface border border-dashed border-line rounded-xl p-12 text-center">
                    <div class="text-5xl mb-3"><x-icon name="trophy" class="h-4 w-4" /></div>
                    <p class="text-base font-bold text-white">{{ __('football.portal.bracket.empty') }}</p>
                </div>
            @endforelse
        </div>
    @endif

</div>
@endsection
