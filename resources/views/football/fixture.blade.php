@extends('layouts.app')

@section('title', __('football.fixture.title', ['name' => $fixture['name'] ?? __('football.fixture.fallback')]))

@section('content')
<div class="space-y-8" x-data="matchCenter()">

    {{-- Breadcrumb & Back --}}
    <div class="flex items-center justify-between">
        <a href="{{ url()->previous() ?? route('football.index') }}" class="group text-xs font-bold text-body hover:text-accent transition-colors flex items-center gap-2 bg-surface border border-line hover:border-line px-3.5 py-2 rounded-xl">
            <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4 -ml-0.5">
                <path d="M14 7l-5 5 5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            {{ __('football.fixture.back') }}
        </a>
        <div class="flex items-center gap-2 text-xs text-body font-semibold">
            <span>{{ $league['name'] ?? 'Liga' }}</span>
            <span class="w-1 h-1 rounded-lg bg-surface"></span>
            <span class="text-accent">{{ $season['name'] ?? 'Musim' }}</span>
        </div>
    </div>

    @php
        $homeTeam = $fixture['home_team'] ?? $home_team ?? [];
        $awayTeam = $fixture['away_team'] ?? $away_team ?? [];

        // Match Scores
        $currentScore = collect($scores)->firstWhere('description', 'CURRENT')
                     ?? collect($scores)->firstWhere('description', '2ND_HALF')
                     ?? collect($scores)->firstWhere('description', '1ST_HALF');

        $homeGoals = $currentScore['home_goals'] ?? '-';
        $awayGoals = $currentScore['away_goals'] ?? '-';

        $homeName = $homeTeam['name'] ?? explode(' vs ', $fixture['name'])[0] ?? 'Home';
        $awayName = $awayTeam['name'] ?? explode(' vs ', $fixture['name'])[1] ?? 'Away';

        $matchState = $fixture['state'] ?? $state ?? [];
        $stateCode = $matchState['short_name'] ?? $matchState['state'] ?? '';
        $stateName = $matchState['name'] ?? $fixture['result_info'] ?? 'Pertandingan';
        // Live short_names as the `states` table stores them — '1H'/'2H'/'LIVE' are not real codes.
        $isLive = in_array($stateCode, ['1st', '2nd', 'HT', 'BRK', 'et', 'ETB', '2et', 'PEN', 'PENB']);

        $homeLineup = $home_lineup ?? [];
        $awayLineup = $away_lineup ?? [];

        $homeXI = $homeLineup['starting_xi'] ?? [];
        $homeBench = $homeLineup['bench'] ?? [];
        $homeFormation = $homeLineup['formation'] ?? '4-3-3';

        $awayXI = $awayLineup['starting_xi'] ?? [];
        $awayBench = $awayLineup['bench'] ?? [];
        $awayFormation = $awayLineup['formation'] ?? '4-3-3';

        // Group Home XI by Row (Top half: Row 1 = GK down to Row 4 = FW)
        $homeRows = collect($homeXI)->groupBy(function($item) {
            return $item['row'] ?? 1;
        })->sortKeys();

        // Group Away XI by Row reversed (Bottom half: Row 4 = FW down to Row 1 = GK at the bottom)
        $awayRows = collect($awayXI)->groupBy(function($item) {
            return $item['row'] ?? 1;
        })->sortKeysDesc();

        // Aggregate Match Events per Player for Badges on Pitch & Bench
        $playerEventsMap = [];
        foreach ($events as $ev) {
            $pId = $ev['player_id'] ?? null;
            $relPId = $ev['related_player_id'] ?? null;
            $typeId = $ev['type_id'] ?? 0;
            $typeName = strtolower($ev['event_type_name'] ?? '');
            $min = $ev['minute'] ?? 0;

            // 1. Goal Scorer & Assist
            if (in_array($typeId, [14, 15, 16, 17]) || str_contains($typeName, 'goal')) {
                if ($pId) {
                    $playerEventsMap[$pId]['goals'] = ($playerEventsMap[$pId]['goals'] ?? 0) + 1;
                    $playerEventsMap[$pId]['list'][] = __('football.fixture.badge.goal', ['minute' => $min]);
                }
                if ($relPId) {
                    $playerEventsMap[$relPId]['assists'] = ($playerEventsMap[$relPId]['assists'] ?? 0) + 1;
                    $playerEventsMap[$relPId]['list'][] = __('football.fixture.badge.assist', ['minute' => $min]);
                }
            }
            // 2. Substitution
            elseif ($typeId == 18 || str_contains($typeName, 'sub')) {
                if ($pId) {
                    $playerEventsMap[$pId]['sub_in'] = $min;
                    $playerEventsMap[$pId]['list'][] = __('football.fixture.badge.sub_in', ['minute' => $min]);
                }
                if ($relPId) {
                    $playerEventsMap[$relPId]['sub_out'] = $min;
                    $playerEventsMap[$relPId]['list'][] = __('football.fixture.badge.sub_out', ['minute' => $min]);
                }
            }
            // 3. Yellow Card
            elseif ($typeId == 19 || str_contains($typeName, 'yellow') || str_contains($typeName, 'kuning')) {
                if ($pId) {
                    $playerEventsMap[$pId]['yellow_cards'] = ($playerEventsMap[$pId]['yellow_cards'] ?? 0) + 1;
                    $playerEventsMap[$pId]['list'][] = __('football.fixture.badge.yellow', ['minute' => $min]);
                }
            }
            // 4. Red Card / Yellow-Red
            elseif ($typeId == 20 || $typeId == 21 || str_contains($typeName, 'red') || str_contains($typeName, 'merah')) {
                if ($pId) {
                    $playerEventsMap[$pId]['red_cards'] = ($playerEventsMap[$pId]['red_cards'] ?? 0) + 1;
                    $playerEventsMap[$pId]['list'][] = __('football.fixture.badge.red', ['minute' => $min]);
                }
            }
        }
    @endphp

    {{-- BROADCAST SCOREBOARD HERO --}}
    <div class="bg-surface rounded-xl p-6 sm:p-10 text-white border border-line relative overflow-hidden">
        {{-- Ambient Backlight --}}

        <div class="relative z-10">
            {{-- Top State & Kickoff --}}
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-2">
                    @if($stateCode)
                        <span class="inline-flex items-center gap-1.5 font-mono font-bold text-xs px-4 py-1.5 rounded-lg uppercase tracking-wider
 {{ $isLive ? 'text-white ring-2 ring-red-400/50' : '' }}
                            {{ in_array($stateCode, ['FT', 'AET', 'FTP']) ? 'bg-surface text-white border border-line' : '' }}
                            {{ in_array($stateCode, ['NS', 'TBA']) ? 'text-steel border border-line' : '' }}
                            {{ !in_array($stateCode, ['1st', '2nd', 'HT', 'BRK', 'et', 'ETB', '2et', 'PEN', 'PENB', 'FT', 'AET', 'FTP', 'NS', 'TBA']) ? 'bg-surface text-white' : '' }}
                        ">
                            <span class="w-2 h-2 rounded-full {{ $isLive ? 'bg-white' : 'bg-primary' }}"></span>
                            {{ $stateCode }} • {{ $stateName }}
                        </span>
                    @else
                        <span class="inline-block bg-surface text-xs font-bold px-3 py-1 rounded-lg uppercase tracking-wider text-white">
                            {{ $fixture['result_info'] ?? __('football.fixture.fallback') }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-body mt-2.5 font-medium">
                    {{ $fixture['starting_at'] ? \Illuminate\Support\Carbon::parse($fixture['starting_at'], 'UTC')->setTimezone('Asia/Jakarta')->locale(app()->getLocale())->translatedFormat('l, d F Y • H:i') . ' WIB' : __('football.fixture.tbd') }}
                </p>
            </div>

            {{-- Teams & Score Display --}}
            <div class="flex flex-col sm:flex-row items-center justify-around gap-8 text-center">
                {{-- Home Team --}}
                <div class="flex-1 flex flex-col items-center gap-3">
                    @if(!empty($homeTeam['id']))
                        <a href="{{ route('football.team', $homeTeam['id']) }}" class="group block">
                            @if(!empty($homeTeam['image_path']))
                                <img src="{{ $homeTeam['image_path'] }}" alt="{{ $homeName }}" class="w-20 h-20 sm:w-24 sm:h-24 object-contain drop- group- mx-auto">
                            @else
                                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl bg-surface flex items-center justify-center text-accent mx-auto">
                                    <svg viewBox="0 0 24 24" fill="none" class="w-10 h-10"><path d="M12 3l7 2.4v5.4c0 4.5-3 8.2-7 9.5-4-1.3-7-5-7-9.5V5.4L12 3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                                </div>
                            @endif
                            <h2 class="text-xl sm:text-2xl font-bold tracking-tight mt-3 text-white group-hover:text-accent transition-colors">{{ $homeName }}</h2>
                        </a>
                    @else
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl bg-surface flex items-center justify-center text-accent mx-auto">
                            <svg viewBox="0 0 24 24" fill="none" class="w-10 h-10"><path d="M12 3l7 2.4v5.4c0 4.5-3 8.2-7 9.5-4-1.3-7-5-7-9.5V5.4L12 3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-bold tracking-tight mt-3 text-white">{{ $homeName }}</h2>
                    @endif
                    <span class="text-xs text-accent font-bold uppercase tracking-wider px-3 py-0.5 rounded-lg border border-line">{{ __('football.fixture.home_label') }}</span>
                </div>

                {{-- Center Scoreboard Box --}}
                <div class="bg-ink px-10 py-5 rounded-xl border border-line flex items-center gap-6">
                    <span class="text-6xl sm:text-7xl font-bold font-mono tracking-tight text-white">{{ $homeGoals }}</span>
                    <span class="text-3xl text-muted font-bold">:</span>
                    <span class="text-6xl sm:text-7xl font-bold font-mono tracking-tight text-white">{{ $awayGoals }}</span>
                </div>

                {{-- Away Team --}}
                <div class="flex-1 flex flex-col items-center gap-3">
                    @if(!empty($awayTeam['id']))
                        <a href="{{ route('football.team', $awayTeam['id']) }}" class="group block">
                            @if(!empty($awayTeam['image_path']))
                                <img src="{{ $awayTeam['image_path'] }}" alt="{{ $awayName }}" class="w-20 h-20 sm:w-24 sm:h-24 object-contain drop- group- mx-auto">
                            @else
                                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl bg-surface flex items-center justify-center text-steel mx-auto">
                                    <svg viewBox="0 0 24 24" fill="none" class="w-10 h-10"><path d="M12 3l7 2.4v5.4c0 4.5-3 8.2-7 9.5-4-1.3-7-5-7-9.5V5.4L12 3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                                </div>
                            @endif
                            <h2 class="text-xl sm:text-2xl font-bold tracking-tight mt-3 text-white group-hover:text-accent transition-colors">{{ $awayName }}</h2>
                        </a>
                    @else
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl bg-surface flex items-center justify-center text-steel mx-auto">
                            <svg viewBox="0 0 24 24" fill="none" class="w-10 h-10"><path d="M12 3l7 2.4v5.4c0 4.5-3 8.2-7 9.5-4-1.3-7-5-7-9.5V5.4L12 3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-bold tracking-tight mt-3 text-white">{{ $awayName }}</h2>
                    @endif
                    <span class="text-xs text-steel font-bold uppercase tracking-wider px-3 py-0.5 rounded-lg border border-line">{{ __('football.fixture.away_label') }}</span>
                </div>
            </div>

            {{-- Goal scorers / red cards / missed penalties per side --}}
            @php
                $fmtMin = function ($ev) {
                    $m = $ev['minute'] ?? null;
                    if ($m === null || $m === '') return '';
                    $ex = $ev['extra_minute'] ?? null;
                    return $m . ($ex ? '+' . $ex : '') . "'";
                };
                $homeScorers = [];
                $awayScorers = [];
                foreach ($events as $ev) {
                    $tn   = strtolower($ev['event_type_name'] ?? '');
                    $tid  = $ev['type_id'] ?? 0;
                    $nm   = $ev['player_name'] ?? __('football.fixture.player');
                    $isHome = !empty($ev['is_home']);
                    $extra = strtolower(($ev['info'] ?? '') . ' ' . ($ev['addition'] ?? '') . ' ' . $tn);

                    $isMissed = $tid == 17 || str_contains($tn, 'missed');
                    $isRed    = in_array($tid, [20, 21]) || str_contains($tn, 'red') || str_contains($tn, 'merah');
                    $isOwn    = str_contains($extra, 'own');
                    $isPen    = str_contains($extra, 'penalt');
                    $isGoal   = in_array($tid, [14, 15, 16]) || (str_contains($tn, 'goal') && !$isMissed);

                    $line = null;
                    $side = $isHome;
                    if ($isMissed) {
                        $line = ['icon' => \App\Support\Icon::svg('cross', 'h-3.5 w-3.5'), 'name' => $nm, 'min' => $fmtMin($ev), 'note' => __('football.fixture.missed_penalty'), 'cls' => 'text-accent', 'sort' => (int)($ev['minute'] ?? 0)];
                    } elseif ($isGoal) {
                        $tag = $isOwn ? 'OG' : ($isPen ? 'P' : '');
                        $line = ['icon' => \App\Support\Icon::svg('ball', 'h-3.5 w-3.5'), 'name' => $nm, 'min' => $fmtMin($ev), 'note' => $tag, 'cls' => 'text-white', 'sort' => (int)($ev['minute'] ?? 0)];
                        $side = $isOwn ? !$isHome : $isHome; // own goals credited to the opponent
                    } elseif ($isRed) {
                        $line = ['icon' => \App\Support\Icon::svg('card-red', 'h-3.5 w-3.5'), 'name' => $nm, 'min' => $fmtMin($ev), 'note' => '', 'cls' => 'text-accent', 'sort' => (int)($ev['minute'] ?? 0)];
                    }
                    if ($line) {
                        if ($side) $homeScorers[] = $line; else $awayScorers[] = $line;
                    }
                }
                usort($homeScorers, fn ($a, $b) => $a['sort'] <=> $b['sort']);
                usort($awayScorers, fn ($a, $b) => $a['sort'] <=> $b['sort']);
            @endphp
            @if(count($homeScorers) || count($awayScorers))
                <div class="mt-8 grid grid-cols-2 gap-4 sm:gap-10 max-w-2xl mx-auto">
                    <div class="space-y-1.5">
                        @foreach($homeScorers as $g)
                            <div class="flex items-center justify-end gap-2 text-xs sm:text-sm">
                                <span class="{{ $g['cls'] }} font-semibold truncate">{{ $g['name'] }}</span>
                                @if($g['note'])<span class="text-xs text-muted font-bold">({{ $g['note'] }})</span>@endif
                                <span class="font-mono text-body shrink-0">{{ $g['min'] }}</span>
                                <span class="shrink-0">{!! $g['icon'] !!}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="space-y-1.5">
                        @foreach($awayScorers as $g)
                            <div class="flex items-center gap-2 text-xs sm:text-sm">
                                <span class="shrink-0">{!! $g['icon'] !!}</span>
                                <span class="font-mono text-body shrink-0">{{ $g['min'] }}</span>
                                @if($g['note'])<span class="text-xs text-muted font-bold">({{ $g['note'] }})</span>@endif
                                <span class="{{ $g['cls'] }} font-semibold truncate">{{ $g['name'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Match Meta (Venue & Referee) --}}
            @if($venue || count($referees) > 0)
                <div class="mt-10 pt-5 border-t border-line flex flex-wrap items-center justify-center gap-x-8 gap-y-3 text-xs text-body font-medium">
                    @if($venue)
                        <span class="flex items-center gap-2">
                            <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4 text-accent shrink-0"><path d="M12 21s6-5.3 6-10a6 6 0 10-12 0c0 4.7 6 10 6 10z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="11" r="2.2" stroke="currentColor" stroke-width="1.6"/></svg>
                            <span><strong class="text-white">{{ __('football.fixture.venue') }}</strong> {{ $venue['name'] }} ({{ $venue['city_name'] ?? '' }})</span>
                        </span>
                    @endif
                    @if(count($referees) > 0)
                        <span class="flex items-center gap-2">
                            <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4 text-accent shrink-0"><circle cx="10" cy="13.5" r="4.5" stroke="currentColor" stroke-width="1.6"/><path d="M14.2 11.4H21v2.3a1 1 0 01-1 1h-3.2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 9V6.2h3.4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span><strong class="text-white">{{ __('football.fixture.referee') }}</strong> {{ $referees[0]['name'] ?? __('football.fixture.referee_fallback') }}</span>
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- MAIN 2-COLUMN LAYOUT --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        {{-- LEFT COLUMN: FULL 22-PLAYER PITCH & MATCH EVENTS --}}
        <div class="lg:col-span-8 space-y-8">

            {{-- SECTION 1: FULL 22-PLAYER 2D FOOTBALL PITCH --}}
            <div class="bg-surface border border-line rounded-xl p-6 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-line">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-line text-accent">
                            <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5"><rect x="3.5" y="4.5" width="17" height="15" rx="1.5" stroke="currentColor" stroke-width="1.6"/><path d="M12 4.5v15M3.5 9.5H6M3.5 14.5H6M20.5 9.5H18M20.5 14.5H18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="12" cy="12" r="2.4" stroke="currentColor" stroke-width="1.6"/></svg>
                        </span>
                        <div>
                            <span class="kicker block text-xs font-bold uppercase text-primary">{{ __('football.fixture.lineup_kicker') }}</span>
                            <h3 class="text-lg font-bold text-white leading-tight">{{ __('football.fixture.lineup_heading') }}</h3>
                        </div>
                    </div>

                    {{-- Formations Indicator --}}
                    <div class="flex items-center gap-3 text-xs font-bold">
                        <span class="px-3 py-1.5 rounded-lg border border-line text-white flex items-center gap-1.5">
                            <svg viewBox="0 0 24 24" fill="none" class="w-3.5 h-3.5 shrink-0"><path d="M12 3l7 2.4v5.4c0 4.5-3 8.2-7 9.5-4-1.3-7-5-7-9.5V5.4L12 3z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                            <span class="truncate max-w-[110px]">{{ $homeName }}</span>
                            <span class="font-mono font-bold">{{ $homeFormation }}</span>
                        </span>
                        <span class="px-3 py-1.5 rounded-lg border border-line text-white flex items-center gap-1.5">
                            <svg viewBox="0 0 24 24" fill="none" class="w-3.5 h-3.5 shrink-0"><path d="M12 3l7 2.4v5.4c0 4.5-3 8.2-7 9.5-4-1.3-7-5-7-9.5V5.4L12 3z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                            <span class="truncate max-w-[110px]">{{ $awayName }}</span>
                            <span class="font-mono font-bold">{{ $awayFormation }}</span>
                        </span>
                    </div>
                </div>

                {{-- FULL PITCH CONTAINER (22 PLAYERS LIVE COMBAT) --}}
                <div class="relative flex min-h-[820px] w-full flex-col justify-between overflow-hidden rounded-xl border border-line bg-surface p-6">
                    
                    {{-- FIELD MARKINGS (TOP & BOTTOM PENALTY BOXES + CENTER CIRCLE) --}}
                    {{-- Top Goal Area & Box (Home) --}}
                    <div class="absolute inset-x-12 top-0 h-28 border-b border-x border-line rounded-b-xl pointer-events-none"></div>
                    <div class="absolute inset-x-24 top-0 h-12 border-b border-x border-line rounded-b-lg pointer-events-none"></div>

                    {{-- Center Circle & Halfway Line --}}
                    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-36 h-36 rounded-lg border border-line pointer-events-none"></div>
                    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-line pointer-events-none"></div>
                    <div class="absolute inset-x-0 top-1/2 -translate-y-1/2 h-px bg-line pointer-events-none"></div>

                    {{-- Bottom Goal Area & Box (Away) --}}
                    <div class="absolute inset-x-12 bottom-0 h-28 border-t border-x border-line rounded-t-xl pointer-events-none"></div>
                    <div class="absolute inset-x-24 bottom-0 h-12 border-t border-x border-line rounded-t-lg pointer-events-none"></div>

                    {{-- TOP HALF: HOME TEAM STARTING XI --}}
                    <div class="relative z-10 space-y-6 pb-6 border-b border-white/10">
                        <div class="text-center">
                            <span class="inline-flex items-center gap-2 text-xs font-mono font-bold uppercase tracking-widest text-accent bg-ink px-3 py-1 rounded-lg border border-line">
                                <span class="w-2 h-2 rounded-full "></span>
                                {{ $homeName }} ({{ $homeFormation }})
                            </span>
                        </div>

                        @if(count($homeXI) > 0)
                            <div class="flex flex-col justify-between space-y-6">
                                @foreach($homeRows as $rowIndex => $playersInRow)
                                    <div class="flex items-center justify-around w-full">
                                        @foreach($playersInRow as $p)
                                            @php
                                                $pId = $p['player_id'] ?? $p['id'];
                                                $evStats = $playerEventsMap[$pId] ?? null;
                                                $pEventsList = $evStats['list'] ?? [];
                                            @endphp
                                            <button type="button"
                                                    title="{{ $p['detailed_position_name'] ?: $p['position_name'] }}"
                                                    @click="openModal({{ json_encode($p) }}, '{{ addslashes($homeName) }}', {{ json_encode($pEventsList) }})"
                                                    class="group flex flex-col items-center text-center focus:outline-none relative">
                                                
                                                <div class="relative">
                                                    @if(!empty($p['player_image']))
                                                        <img src="{{ $p['player_image'] }}" alt="{{ $p['player_name'] }}" class="w-12 h-12 rounded-lg object-cover border-2 border-line bg-surface group-hover:ring-4 group-hover:ring-line transition-all">
                                                    @else
                                                        <div class="w-12 h-12 rounded-lg bg-surface border-2 border-line text-accent flex items-center justify-center font-bold text-xs">
                                                            {{ $p['jersey_number'] ?? '#' }}
                                                        </div>
                                                    @endif

                                                    {{-- Jersey Number Badge --}}
                                                    <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-lg bg-ink text-accent font-mono font-bold text-xs flex items-center justify-center border border-line">
                                                        {{ $p['jersey_number'] ?? '•' }}
                                                    </span>

                                                    {{-- Rating Badge (FixtureLineupDetail type_id 118) --}}
                                                    @if(!empty($p['rating']))
                                                        @php
                                                            $rt = (float) $p['rating'];
                                                            $rtCls = $rt >= 7 ? 'bg-primary text-white' : ($rt >= 6 ? 'bg-gold text-muted' : 'bg-accent text-white');
                                                        @endphp
                                                        <span class="absolute -bottom-1 -left-1 h-5 min-w-[22px] px-1 rounded-lg {{ $rtCls }} font-mono font-bold text-xs flex items-center justify-center border border-line z-20" title="Rating {{ number_format($rt, 2) }}">
                                                            {{ number_format($rt, 1) }}
                                                        </span>
                                                    @endif

                                                    {{-- EVENT BADGES OVERLAY (GOAL, ASSIST, CARD, SUB) --}}
                                                    @if($evStats)
                                                        {{-- Top-Right: Goal & Assist Badges --}}
                                                        <div class="absolute -top-2.5 -right-2.5 flex items-center gap-0.5 z-20">
                                                            @if(!empty($evStats['goals']))
                                                                <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] px-1 rounded-lg bg-primary text-white font-bold text-xs ring-2 ring-line" title="Gol ({{ $evStats['goals'] }})">
                                                                    <x-icon name="ball" class="h-4 w-4" />{{ $evStats['goals'] > 1 ? $evStats['goals'] : '' }}
                                                                </span>
                                                            @endif
                                                            @if(!empty($evStats['assists']))
                                                                <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] px-1 rounded-lg bg-steel text-muted font-bold text-xs ring-2 ring-line" title="Assist ({{ $evStats['assists'] }})">
                                                                    <x-icon name="boot" class="h-4 w-4" />{{ $evStats['assists'] > 1 ? $evStats['assists'] : '' }}
                                                                </span>
                                                            @endif
                                                        </div>

                                                        {{-- Top-Left: Cards & Sub Badges --}}
                                                        <div class="absolute -top-2.5 -left-2.5 flex items-center gap-0.5 z-20">
                                                            @if(!empty($evStats['red_cards']))
                                                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] rounded-lg bg-accent text-white font-bold text-xs ring-2 ring-line" title="{{ __('football.fixture.red_card') }}">
                                                                    <x-icon name="card-red" class="h-4 w-4" />
                                                                </span>
                                                            @elseif(!empty($evStats['yellow_cards']))
                                                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] rounded-lg bg-gold text-muted font-bold text-xs ring-2 ring-line" title="{{ __('football.fixture.yellow_card', ['count' => $evStats['yellow_cards']]) }}">
                                                                    <x-icon name="card-yellow" class="h-4 w-4" />{{ $evStats['yellow_cards'] > 1 ? '2' : '' }}
                                                                </span>
                                                            @endif
                                                            @if(!empty($evStats['sub_out']))
                                                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] rounded-lg bg-accent text-white font-bold text-xs ring-2 ring-line" title="{{ __('football.fixture.sub_out_at', ['minute' => $evStats['sub_out']]) }}">
                                                                    <x-icon name="out" class="h-4 w-4" />
                                                                </span>
                                                            @elseif(!empty($evStats['sub_in']))
                                                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] rounded-lg bg-primary text-white font-bold text-xs ring-2 ring-line" title="{{ __('football.fixture.sub_in_at', ['minute' => $evStats['sub_in']]) }}">
                                                                    <x-icon name="in" class="h-4 w-4" />
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>

                                                <span class="mt-1 px-2 py-0.5 rounded-lg bg-ink text-white font-bold text-xs border border-line group-hover:bg-primary group-hover:text-white transition-colors max-w-[80px] truncate">
                                                    {{ $p['player_name'] ?: __('football.fixture.player_fallback', ['id' => $p['player_id']]) }}
                                                </span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-white text-center py-4">{{ __('football.fixture.lineup_home_empty') }}</p>
                        @endif
                    </div>

                    {{-- BOTTOM HALF: AWAY TEAM STARTING XI --}}
                    <div class="relative z-10 space-y-6 pt-6">
                        @if(count($awayXI) > 0)
                            <div class="flex flex-col justify-between space-y-6">
                                @foreach($awayRows as $rowIndex => $playersInRow)
                                    <div class="flex items-center justify-around w-full">
                                        @foreach($playersInRow as $p)
                                            @php
                                                $pId = $p['player_id'] ?? $p['id'];
                                                $evStats = $playerEventsMap[$pId] ?? null;
                                                $pEventsList = $evStats['list'] ?? [];
                                            @endphp
                                            <button type="button"
                                                    title="{{ $p['detailed_position_name'] ?: $p['position_name'] }}"
                                                    @click="openModal({{ json_encode($p) }}, '{{ addslashes($awayName) }}', {{ json_encode($pEventsList) }})"
                                                    class="group flex flex-col items-center text-center focus:outline-none relative">
                                                
                                                <div class="relative">
                                                    @if(!empty($p['player_image']))
                                                        <img src="{{ $p['player_image'] }}" alt="{{ $p['player_name'] }}" class="w-12 h-12 rounded-lg object-cover border-2 border-line bg-surface group-hover:ring-4 group-hover:ring-blue-400/50 transition-all">
                                                    @else
                                                        <div class="w-12 h-12 rounded-lg bg-surface border-2 border-line text-steel flex items-center justify-center font-bold text-xs">
                                                            {{ $p['jersey_number'] ?? '#' }}
                                                        </div>
                                                    @endif

                                                    {{-- Jersey Number Badge --}}
                                                    <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-lg bg-ink text-steel font-mono font-bold text-xs flex items-center justify-center border border-line">
                                                        {{ $p['jersey_number'] ?? '•' }}
                                                    </span>

                                                    {{-- Rating Badge (FixtureLineupDetail type_id 118) --}}
                                                    @if(!empty($p['rating']))
                                                        @php
                                                            $rt = (float) $p['rating'];
                                                            $rtCls = $rt >= 7 ? 'bg-primary text-white' : ($rt >= 6 ? 'bg-gold text-muted' : 'bg-accent text-white');
                                                        @endphp
                                                        <span class="absolute -bottom-1 -left-1 h-5 min-w-[22px] px-1 rounded-lg {{ $rtCls }} font-mono font-bold text-xs flex items-center justify-center border border-line z-20" title="Rating {{ number_format($rt, 2) }}">
                                                            {{ number_format($rt, 1) }}
                                                        </span>
                                                    @endif

                                                    {{-- EVENT BADGES OVERLAY (GOAL, ASSIST, CARD, SUB) --}}
                                                    @if($evStats)
                                                        {{-- Top-Right: Goal & Assist Badges --}}
                                                        <div class="absolute -top-2.5 -right-2.5 flex items-center gap-0.5 z-20">
                                                            @if(!empty($evStats['goals']))
                                                                <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] px-1 rounded-lg bg-primary text-white font-bold text-xs ring-2 ring-line" title="Gol ({{ $evStats['goals'] }})">
                                                                    <x-icon name="ball" class="h-4 w-4" />{{ $evStats['goals'] > 1 ? $evStats['goals'] : '' }}
                                                                </span>
                                                            @endif
                                                            @if(!empty($evStats['assists']))
                                                                <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] px-1 rounded-lg bg-steel text-muted font-bold text-xs ring-2 ring-line" title="Assist ({{ $evStats['assists'] }})">
                                                                    <x-icon name="boot" class="h-4 w-4" />{{ $evStats['assists'] > 1 ? $evStats['assists'] : '' }}
                                                                </span>
                                                            @endif
                                                        </div>

                                                        {{-- Top-Left: Cards & Sub Badges --}}
                                                        <div class="absolute -top-2.5 -left-2.5 flex items-center gap-0.5 z-20">
                                                            @if(!empty($evStats['red_cards']))
                                                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] rounded-lg bg-accent text-white font-bold text-xs ring-2 ring-line" title="{{ __('football.fixture.red_card') }}">
                                                                    <x-icon name="card-red" class="h-4 w-4" />
                                                                </span>
                                                            @elseif(!empty($evStats['yellow_cards']))
                                                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] rounded-lg bg-gold text-muted font-bold text-xs ring-2 ring-line" title="{{ __('football.fixture.yellow_card', ['count' => $evStats['yellow_cards']]) }}">
                                                                    <x-icon name="card-yellow" class="h-4 w-4" />{{ $evStats['yellow_cards'] > 1 ? '2' : '' }}
                                                                </span>
                                                            @endif
                                                            @if(!empty($evStats['sub_out']))
                                                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] rounded-lg bg-accent text-white font-bold text-xs ring-2 ring-line" title="{{ __('football.fixture.sub_out_at', ['minute' => $evStats['sub_out']]) }}">
                                                                    <x-icon name="out" class="h-4 w-4" />
                                                                </span>
                                                            @elseif(!empty($evStats['sub_in']))
                                                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] rounded-lg bg-primary text-white font-bold text-xs ring-2 ring-line" title="{{ __('football.fixture.sub_in_at', ['minute' => $evStats['sub_in']]) }}">
                                                                    <x-icon name="in" class="h-4 w-4" />
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>

                                                <span class="mt-1 px-2 py-0.5 rounded-lg bg-ink text-white font-bold text-xs border border-line group-hover:bg-steel group-hover:text-muted transition-colors max-w-[80px] truncate">
                                                    {{ $p['player_name'] ?: __('football.fixture.player_fallback', ['id' => $p['player_id']]) }}
                                                </span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-white text-center py-4">{{ __('football.fixture.lineup_away_empty') }}</p>
                        @endif

                        <div class="text-center pt-2">
                            <span class="inline-flex items-center gap-2 text-xs font-mono font-bold uppercase tracking-widest text-steel bg-ink px-3 py-1 rounded-lg border border-line">
                                <span class="w-2 h-2 rounded-full text-ink"></span>
                                {{ $awayName }} ({{ $awayFormation }})
                            </span>
                        </div>
                    </div>

                </div>

                {{-- BOTH TEAMS BENCH LISTS --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                    {{-- Home Bench --}}
                    <div class="bg-ink border border-line rounded-xl p-4 space-y-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-accent flex items-center gap-2">
                            <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4 shrink-0"><path d="M4 8.5h13l-3-3M20 15.5H7l3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            {{ __('football.fixture.bench', ['team' => $homeName]) }}
                        </span>
                        <div class="grid grid-cols-2 gap-2">
                            @forelse($homeBench as $p)
                                @php
                                    $pId = $p['player_id'] ?? $p['id'];
                                    $evStats = $playerEventsMap[$pId] ?? null;
                                    $pEventsList = $evStats['list'] ?? [];
                                @endphp
                                <button type="button"
                                        title="{{ $p['detailed_position_name'] ?: $p['position_name'] }}"
                                        @click="openModal({{ json_encode($p) }}, '{{ addslashes($homeName) }}', {{ json_encode($pEventsList) }})"
                                        class="flex items-center gap-2 p-2 rounded-xl bg-surface hover:bg-surface border border-line text-left transition-colors relative">
                                    @if(!empty($p['player_image']))
                                        <img src="{{ $p['player_image'] }}" alt="{{ $p['player_name'] }}" class="w-7 h-7 rounded-lg object-cover border border-line flex-shrink-0">
                                    @else
                                        <div class="w-7 h-7 rounded-lg bg-surface text-body font-mono font-bold text-xs flex items-center justify-center flex-shrink-0">
                                            {{ $p['jersey_number'] ?? '•' }}
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <span class="text-xs font-bold text-white block truncate hover:text-accent">{{ $p['player_name'] }}</span>
                                        <div class="flex items-center gap-1">
                                            <span class="text-xs text-muted font-mono">No. {{ $p['jersey_number'] ?? '-' }}</span>
                                            @if(!empty($p['rating']))
                                                @php
                                                    $rt = (float) $p['rating'];
                                                    $rtCls = $rt >= 7 ? 'text-accent border-line' : ($rt >= 6 ? 'text-gold border-line' : 'text-accent border-line');
                                                @endphp
                                                <span class="text-xs font-mono font-bold px-1 rounded-lg border {{ $rtCls }}" title="Rating {{ number_format($rt, 2) }}">{{ number_format($rt, 1) }}</span>
                                            @endif
                                            @if($evStats)
                                                @if(!empty($evStats['goals']))
                                                    <span class="text-xs"><x-icon name="ball" class="h-4 w-4" /></span>
                                                @endif
                                                @if(!empty($evStats['sub_in']))
                                                    <span class="text-xs"><x-icon name="in" class="h-4 w-4" /></span>
                                                @endif
                                                @if(!empty($evStats['yellow_cards']))
                                                    <span class="text-xs"><x-icon name="card-yellow" class="h-4 w-4" /></span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </button>
                            @empty
                                <p class="text-xs text-muted col-span-2 italic">{{ __('football.fixture.bench_empty') }}</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Away Bench --}}
                    <div class="bg-ink border border-line rounded-xl p-4 space-y-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-steel flex items-center gap-2">
                            <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4 shrink-0"><path d="M4 8.5h13l-3-3M20 15.5H7l3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            {{ __('football.fixture.bench', ['team' => $awayName]) }}
                        </span>
                        <div class="grid grid-cols-2 gap-2">
                            @forelse($awayBench as $p)
                                @php
                                    $pId = $p['player_id'] ?? $p['id'];
                                    $evStats = $playerEventsMap[$pId] ?? null;
                                    $pEventsList = $evStats['list'] ?? [];
                                @endphp
                                <button type="button"
                                        title="{{ $p['detailed_position_name'] ?: $p['position_name'] }}"
                                        @click="openModal({{ json_encode($p) }}, '{{ addslashes($awayName) }}', {{ json_encode($pEventsList) }})"
                                        class="flex items-center gap-2 p-2 rounded-xl bg-surface hover:bg-surface border border-line text-left transition-colors relative">
                                    @if(!empty($p['player_image']))
                                        <img src="{{ $p['player_image'] }}" alt="{{ $p['player_name'] }}" class="w-7 h-7 rounded-lg object-cover border border-line flex-shrink-0">
                                    @else
                                        <div class="w-7 h-7 rounded-lg bg-surface text-body font-mono font-bold text-xs flex items-center justify-center flex-shrink-0">
                                            {{ $p['jersey_number'] ?? '•' }}
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <span class="text-xs font-bold text-white block truncate hover:text-steel">{{ $p['player_name'] }}</span>
                                        <div class="flex items-center gap-1">
                                            <span class="text-xs text-muted font-mono">No. {{ $p['jersey_number'] ?? '-' }}</span>
                                            @if(!empty($p['rating']))
                                                @php
                                                    $rt = (float) $p['rating'];
                                                    $rtCls = $rt >= 7 ? 'text-accent border-line' : ($rt >= 6 ? 'text-gold border-line' : 'text-accent border-line');
                                                @endphp
                                                <span class="text-xs font-mono font-bold px-1 rounded-lg border {{ $rtCls }}" title="Rating {{ number_format($rt, 2) }}">{{ number_format($rt, 1) }}</span>
                                            @endif
                                            @if($evStats)
                                                @if(!empty($evStats['goals']))
                                                    <span class="text-xs"><x-icon name="ball" class="h-4 w-4" /></span>
                                                @endif
                                                @if(!empty($evStats['sub_in']))
                                                    <span class="text-xs"><x-icon name="in" class="h-4 w-4" /></span>
                                                @endif
                                                @if(!empty($evStats['yellow_cards']))
                                                    <span class="text-xs"><x-icon name="card-yellow" class="h-4 w-4" /></span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </button>
                            @empty
                                <p class="text-xs text-muted col-span-2 italic">{{ __('football.fixture.bench_empty') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>

            {{-- SECTION 2: MATCH TIMELINE EVENTS (HOME VS AWAY) --}}
            <div class="bg-surface border border-line rounded-xl p-6 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-line">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-line text-accent">
                            <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.6"/><path d="M12 7.5V12l3 1.7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <span class="kicker block text-xs font-bold uppercase text-primary">{{ __('football.fixture.timeline_kicker') }}</span>
                            <h3 class="text-lg font-bold text-white leading-tight">{{ __('football.fixture.timeline_heading') }}</h3>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 text-xs font-bold">
                        <span class="inline-flex items-center gap-1.5 text-accent">
                            <span class="w-2 h-2 rounded-full "></span>{{ $homeName }}
                        </span>
                        <span class="text-muted">/</span>
                        <span class="inline-flex items-center gap-1.5 text-steel">
                            {{ $awayName }}<span class="w-2 h-2 rounded-full text-ink"></span>
                        </span>
                    </div>
                </div>

                @if(count($events) > 0)
                    <div class="space-y-4 relative">
                        {{-- Center timeline vertical beam --}}
                        <div class="hidden sm:block absolute left-1/2 top-0 bottom-0 w-px bg-line -translate-x-1/2"></div>

                        @foreach($events as $ev)
                            @php
                                $isHome = !empty($ev['is_home']);
                                $isSub = ($ev['type_id'] == 18);
                                $isGoal = in_array($ev['type_id'], [14, 15, 16, 17]) || str_contains(strtolower($ev['event_type_name'] ?? ''), 'goal');
                            @endphp

                            <div class="flex items-center sm:justify-between w-full">

                                {{-- HOME EVENT (LEFT) --}}
                                <div class="w-full sm:w-[45%] {{ $isHome ? 'block' : 'hidden sm:block sm:invisible' }}">
                                    @if($isHome)
                                        <div class="bg-ink border border-line hover:border-line rounded-xl p-4 text-left transition-all">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="space-y-2 flex-1">
                                                    <div class="flex items-center gap-2 text-xs font-semibold text-accent uppercase tracking-wider">
                                                        @if($isGoal) <x-icon name="ball" class="h-4 w-4" /> {{ __('football.fixture.event.goal') }}
                                                        @elseif($isSub) <x-icon name="swap" class="h-4 w-4" /> {{ __('football.fixture.event.substitution') }}
                                                        @elseif($ev['type_id'] == 19) <x-icon name="card-yellow" class="h-4 w-4" /> {{ __('football.fixture.event.yellow') }}
                                                        @elseif($ev['type_id'] == 20) <x-icon name="card-red" class="h-4 w-4" /> {{ __('football.fixture.event.red') }}
                                                        @else <x-icon name="pin" class="h-4 w-4" /> {{ $ev['event_type_name'] ?? __('football.fixture.event.other') }}
                                                        @endif
                                                    </div>

                                                    {{-- Substitution Player Face Cards --}}
                                                    @if($isSub)
                                                        <div class="space-y-2 pt-1">
                                                            <div class="flex items-center gap-2.5">
                                                                @if(!empty($ev['player_image']))
                                                                    <img src="{{ $ev['player_image'] }}" alt="{{ $ev['player_name'] }}" class="w-8 h-8 rounded-lg object-cover border-2 border-line flex-shrink-0">
                                                                @else
                                                                    <div class="w-8 h-8 rounded-lg bg-surface text-accent text-xs font-bold flex items-center justify-center flex-shrink-0"><x-icon name="in" class="h-4 w-4" /></div>
                                                                @endif
                                                                <div>
                                                                    <span class="text-xs font-bold text-accent uppercase">{{ __('football.fixture.event.in') }}</span>
                                                                    <span class="text-xs font-bold text-white block truncate">{{ $ev['player_name'] ?: __('football.fixture.event.sub_in_fallback') }}</span>
                                                                </div>
                                                            </div>
                                                            @if(!empty($ev['related_player_name']))
                                                                <div class="flex items-center gap-2.5 opacity-75">
                                                                    @if(!empty($ev['related_player_image']))
                                                                        <img src="{{ $ev['related_player_image'] }}" alt="{{ $ev['related_player_name'] }}" class="w-7 h-7 rounded-lg object-cover border border-line flex-shrink-0">
                                                                    @else
                                                                        <div class="w-7 h-7 rounded-lg bg-surface text-accent text-xs font-bold flex items-center justify-center flex-shrink-0"><x-icon name="out" class="h-4 w-4" /></div>
                                                                    @endif
                                                                    <div>
                                                                        <span class="text-xs font-bold text-accent uppercase">{{ __('football.fixture.event.out') }}</span>
                                                                        <span class="text-xs font-semibold text-white block truncate">{{ $ev['related_player_name'] }}</span>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    {{-- Goal Scorer & Assist --}}
                                                    @elseif($isGoal)
                                                        <div class="flex items-center gap-3 pt-1">
                                                            @if(!empty($ev['player_image']))
                                                                <img src="{{ $ev['player_image'] }}" alt="{{ $ev['player_name'] }}" class="w-10 h-10 rounded-lg object-cover border-2 border-line flex-shrink-0">
                                                            @else
                                                                <div class="w-10 h-10 rounded-lg bg-surface text-white flex items-center justify-center text-base flex-shrink-0"><x-icon name="ball" class="h-4 w-4" /></div>
                                                            @endif
                                                            <div>
                                                                <span class="text-sm font-semibold text-white block">{{ $ev['player_name'] ?: __('football.fixture.event.scorer_fallback') }}</span>
                                                                @if(!empty($ev['related_player_name']))
                                                                    <p class="text-xs text-body mt-0.5">
                                                                        {{ __('football.fixture.event.assist') }} <strong class="text-white">{{ $ev['related_player_name'] }}</strong>
                                                                    </p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    {{-- Cards --}}
                                                    @else
                                                        <span class="text-xs font-bold text-white block">{{ $ev['player_name'] ?: __('football.fixture.player_fallback', ['id' => $ev['player_id'] ?? '']) }}</span>
                                                    @endif

                                                    @if(!empty($ev['info']))
                                                        <span class="inline-flex items-center gap-1 text-xs font-bold bg-surface text-white px-2.5 py-0.5 rounded-lg border border-line mt-1">
                                                            <svg viewBox="0 0 24 24" fill="none" class="w-3 h-3 text-body shrink-0"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.6"/><path d="M12 11v5M12 8h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                                            {{ $ev['info'] }}
                                                        </span>
                                                    @endif
                                                </div>

                                                @if(!empty($ev['result']))
                                                    <span class="font-mono font-bold text-xs bg-primary text-white px-2.5 py-1 rounded-lg">
                                                        {{ $ev['result'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- CENTER MINUTE BADGE --}}
                                <div class="z-10 flex-shrink-0 mx-2 hidden sm:flex items-center justify-center w-9 h-9 rounded-lg bg-ink text-white font-mono font-bold text-xs border-2 border-line">
                                    {{ $ev['minute'] ?? 0 }}'
                                </div>

                                {{-- AWAY EVENT (RIGHT) --}}
                                <div class="w-full sm:w-[45%] {{ !$isHome ? 'block' : 'hidden sm:block sm:invisible' }}">
                                    @if(!$isHome)
                                        <div class="bg-ink border border-line hover:border-line rounded-xl p-4 text-left transition-all">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="space-y-2 flex-1">
                                                    <div class="flex items-center gap-2 text-xs font-semibold text-steel uppercase tracking-wider">
                                                        @if($isGoal) <x-icon name="ball" class="h-4 w-4" /> {{ __('football.fixture.event.goal') }}
                                                        @elseif($isSub) <x-icon name="swap" class="h-4 w-4" /> {{ __('football.fixture.event.substitution') }}
                                                        @elseif($ev['type_id'] == 19) <x-icon name="card-yellow" class="h-4 w-4" /> {{ __('football.fixture.event.yellow') }}
                                                        @elseif($ev['type_id'] == 20) <x-icon name="card-red" class="h-4 w-4" /> {{ __('football.fixture.event.red') }}
                                                        @else <x-icon name="pin" class="h-4 w-4" /> {{ $ev['event_type_name'] ?? __('football.fixture.event.other') }}
                                                        @endif
                                                    </div>

                                                    {{-- Substitution Player Face Cards --}}
                                                    @if($isSub)
                                                        <div class="space-y-2 pt-1">
                                                            <div class="flex items-center gap-2.5">
                                                                @if(!empty($ev['player_image']))
                                                                    <img src="{{ $ev['player_image'] }}" alt="{{ $ev['player_name'] }}" class="w-8 h-8 rounded-lg object-cover border-2 border-line flex-shrink-0">
                                                                @else
                                                                    <div class="w-8 h-8 rounded-lg bg-surface text-accent text-xs font-bold flex items-center justify-center flex-shrink-0"><x-icon name="in" class="h-4 w-4" /></div>
                                                                @endif
                                                                <div>
                                                                    <span class="text-xs font-bold text-accent uppercase">{{ __('football.fixture.event.in') }}</span>
                                                                    <span class="text-xs font-bold text-white block truncate">{{ $ev['player_name'] ?: __('football.fixture.event.sub_in_fallback') }}</span>
                                                                </div>
                                                            </div>
                                                            @if(!empty($ev['related_player_name']))
                                                                <div class="flex items-center gap-2.5 opacity-75">
                                                                    @if(!empty($ev['related_player_image']))
                                                                        <img src="{{ $ev['related_player_image'] }}" alt="{{ $ev['related_player_name'] }}" class="w-7 h-7 rounded-lg object-cover border border-line flex-shrink-0">
                                                                    @else
                                                                        <div class="w-7 h-7 rounded-lg bg-surface text-accent text-xs font-bold flex items-center justify-center flex-shrink-0"><x-icon name="out" class="h-4 w-4" /></div>
                                                                    @endif
                                                                    <div>
                                                                        <span class="text-xs font-bold text-accent uppercase">{{ __('football.fixture.event.out') }}</span>
                                                                        <span class="text-xs font-semibold text-white block truncate">{{ $ev['related_player_name'] }}</span>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    {{-- Goal Scorer & Assist --}}
                                                    @elseif($isGoal)
                                                        <div class="flex items-center gap-3 pt-1">
                                                            @if(!empty($ev['player_image']))
                                                                <img src="{{ $ev['player_image'] }}" alt="{{ $ev['player_name'] }}" class="w-10 h-10 rounded-lg object-cover border-2 border-line flex-shrink-0">
                                                            @else
                                                                <div class="w-10 h-10 rounded-lg bg-surface text-white flex items-center justify-center text-base flex-shrink-0"><x-icon name="ball" class="h-4 w-4" /></div>
                                                            @endif
                                                            <div>
                                                                <span class="text-sm font-semibold text-white block">{{ $ev['player_name'] ?: __('football.fixture.event.scorer_fallback') }}</span>
                                                                @if(!empty($ev['related_player_name']))
                                                                    <p class="text-xs text-body mt-0.5">
                                                                        {{ __('football.fixture.event.assist') }} <strong class="text-white">{{ $ev['related_player_name'] }}</strong>
                                                                    </p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    {{-- Cards --}}
                                                    @else
                                                        <span class="text-xs font-bold text-white block">{{ $ev['player_name'] ?: __('football.fixture.player_fallback', ['id' => $ev['player_id'] ?? '']) }}</span>
                                                    @endif

                                                    @if(!empty($ev['info']))
                                                        <span class="inline-flex items-center gap-1 text-xs font-bold bg-surface text-white px-2.5 py-0.5 rounded-lg border border-line mt-1">
                                                            <svg viewBox="0 0 24 24" fill="none" class="w-3 h-3 text-body shrink-0"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.6"/><path d="M12 11v5M12 8h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                                            {{ $ev['info'] }}
                                                        </span>
                                                    @endif
                                                </div>

                                                @if(!empty($ev['result']))
                                                    <span class="font-mono font-bold text-xs bg-steel text-muted px-2.5 py-1 rounded-lg">
                                                        {{ $ev['result'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>

                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center py-12 text-body text-sm">{{ __('football.fixture.events_empty') }}</p>
                @endif
            </div>

        </div>

        {{-- RIGHT COLUMN: SCORES BREAKDOWN & MATCH STATS --}}
        <div class="lg:col-span-4 space-y-8">

            {{-- PERIOD SCORES BREAKDOWN (1ST_HALF, 2ND_HALF, CURRENT) --}}
            <div class="bg-surface border border-line rounded-xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-line text-accent">
                        <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5"><rect x="4" y="5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M4 9.5h16M12 9.5V19" stroke="currentColor" stroke-width="1.6"/></svg>
                    </span>
                    <div>
                        <span class="kicker block text-xs font-bold uppercase text-primary">{{ __('football.fixture.scores_kicker') }}</span>
                        <h3 class="text-base font-bold text-white leading-tight">{{ __('football.fixture.scores_heading') }}</h3>
                    </div>
                </div>

                @if(count($scores) > 0)
                    <div class="overflow-hidden rounded-xl border border-line bg-ink">
                        <table class="w-full text-center text-xs font-semibold">
                            <thead class="bg-surface text-body font-bold uppercase tracking-wider border-b border-line text-xs">
                                <tr>
                                    <th class="py-3 px-3 text-accent">{{ $homeTeam['short_code'] ?? __('football.card.home') }}</th>
                                    <th class="py-3 px-3 text-body">{{ __('football.fixture.period') }}</th>
                                    <th class="py-3 px-3 text-steel">{{ $awayTeam['short_code'] ?? __('football.card.away') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line text-white font-medium">
                                @foreach($scores as $sc)
                                    <tr class="hover:bg-surface transition-colors {{ $sc['description'] === 'CURRENT' ? 'bg-surface font-bold' : '' }}">
                                        <td class="py-3 px-3 text-sm font-mono font-bold text-white">{{ $sc['home_goals'] }}</td>
                                        <td class="py-3 px-3 text-body font-bold text-xs">
                                            @if($sc['description'] === '1ST_HALF') {{ __('football.fixture.first_half') }}
                                            @elseif($sc['description'] === '2ND_HALF') {{ __('football.fixture.second_half') }}
                                            @elseif($sc['description'] === 'CURRENT') {{ __('football.fixture.final_score') }}
                                            @else {{ $sc['description'] }}
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-sm font-mono font-bold text-white">{{ $sc['away_goals'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-xs text-body text-center py-6">{{ __('football.fixture.scores_empty') }}</p>
                @endif
            </div>

            {{-- MATCH STATISTICS COMPARISON --}}
            <div class="bg-surface border border-line rounded-xl p-6">
                <div class="flex items-center gap-3 mb-6 pb-3 border-b border-line">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-line text-accent">
                        <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5"><path d="M5 19V11M12 19V6M19 19v-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </span>
                    <div>
                        <span class="kicker block text-xs font-bold uppercase text-primary">{{ __('football.fixture.stats_kicker') }}</span>
                        <h3 class="text-base font-bold text-white leading-tight">{{ __('football.fixture.stats_heading') }}</h3>
                    </div>
                </div>

                @if(count($statistics) > 0)
                    <div class="space-y-5">
                        @foreach($statistics as $st)
                            @php
                                $hVal = $st['home_value'] ?? 0;
                                $aVal = $st['away_value'] ?? 0;
                                $total = $hVal + $aVal;
                                $hPct = $total > 0 ? round(($hVal / $total) * 100) : 50;
                                $aPct = 100 - $hPct;
                            @endphp
                            <div class="space-y-1.5">
                                <div class="flex justify-between items-center text-xs font-bold">
                                    <span class="text-accent font-mono text-sm font-bold">{{ $st['home_text'] }}</span>
                                    <span class="text-body text-xs uppercase tracking-wider">{{ $st['type_name'] }}</span>
                                    <span class="text-steel font-mono text-sm font-bold">{{ $st['away_text'] }}</span>
                                </div>
                                <div class="h-2 w-full bg-ink rounded-lg flex overflow-hidden border border-line">
                                    <div class="bg-primary h-full transition-all" style="width: {{ $hPct }}%"></div>
                                    <div class="bg-steel h-full transition-all text-white" style="width: {{ $aPct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-white text-center py-8">{{ __('football.fixture.stats_empty') }}</p>
                @endif
            </div>

        </div>

    </div>

    {{-- INTERACTIVE IN-MATCH PLAYER STATISTICS MODAL --}}
    <div x-show="showModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-ink"
         style="display: none;">
        
        <div class="bg-surface border border-line rounded-xl p-6 sm:p-8 max-w-lg w-full space-y-6 relative overflow-hidden"
             @click.away="showModal = false">
            
            {{-- Background Glow --}}

            {{-- Modal Header with Player Bio --}}
            <div class="flex items-center justify-between pb-4 border-b border-line relative z-10">
                <div class="flex items-center gap-4">
                    <template x-if="selectedPlayer.player_image">
                        <img :src="selectedPlayer.player_image" :alt="selectedPlayer.player_name" class="w-16 h-16 rounded-xl object-cover border-2 border-line bg-ink flex-shrink-0">
                    </template>
                    <template x-if="!selectedPlayer.player_image">
                        <div class="w-16 h-16 rounded-xl bg-ink border-2 border-line flex items-center justify-center text-accent flex-shrink-0">
                            <svg viewBox="0 0 24 24" fill="none" class="w-8 h-8"><circle cx="12" cy="8.5" r="3.8" stroke="currentColor" stroke-width="1.6"/><path d="M5 19.5c0-3.6 3.1-5.5 7-5.5s7 1.9 7 5.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                        </div>
                    </template>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-lg text-accent border border-line" x-text="@js(__('football.fixture.modal.number', ['number' => '__N__'])).replace('__N__', selectedPlayer.jersey_number || '-')"></span>
                            <span class="text-xs text-body font-bold" x-text="selectedPlayer.team_name"></span>
                        </div>
                        <h3 class="text-lg font-bold text-white mt-1" x-text="selectedPlayer.player_name"></h3>
                        <span class="text-xs text-body font-semibold" x-text="@js(__('football.fixture.modal.position', ['position' => '__P__'])).replace('__P__', selectedPlayer.detailed_position_name || selectedPlayer.position_name || @js(__('football.fixture.player')))"></span>
                    </div>
                </div>

                <button @click="showModal = false" class="text-muted hover:text-white p-2 rounded-xl bg-ink border border-line hover:border-line transition-colors">
                    <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                </button>
            </div>

            {{-- Match Incidents Badges List in Modal --}}
            <template x-if="selectedPlayer.events && selectedPlayer.events.length > 0">
                <div class="space-y-2 relative z-10">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-accent">{{ __('football.fixture.modal.incidents') }}</h4>
                    <div class="flex flex-wrap items-center gap-2">
                        <template x-for="evText in selectedPlayer.events" :key="evText">
                            <span class="px-3 py-1 rounded-xl bg-ink text-white font-bold text-xs border border-line flex items-center gap-1" x-text="evText"></span>
                        </template>
                    </div>
                </div>
            </template>

            {{-- In-Match Player Statistics Grid --}}
            <div class="space-y-3 relative z-10">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-body">{{ __('football.fixture.modal.stats') }}</h4>
                    <template x-if="selectedPlayer.rating">
                        <span class="px-2.5 py-1 rounded-xl text-gold font-mono font-bold text-xs border border-line flex items-center gap-1.5">
                            <svg viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5"><path d="M12 3.5l2.5 5.1 5.6.8-4.05 3.95.96 5.6L12 16.9l-5.01 2.65.96-5.6L3.9 9.4l5.6-.8L12 3.5z"/></svg>
                            {{ __('football.fixture.modal.rating') }} <span x-text="selectedPlayer.rating"></span>
                        </span>
                    </template>
                </div>

                <template x-if="selectedPlayer.stats && selectedPlayer.stats.length > 0">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 max-h-60 overflow-y-auto pr-1">
                        <template x-for="stat in selectedPlayer.stats" :key="stat.type_id">
                            <div class="bg-ink border border-line p-3 rounded-xl flex flex-col justify-between">
                                <span class="text-xs text-body font-semibold uppercase tracking-wider truncate" x-text="stat.type_name"></span>
                                <span class="text-base font-mono font-bold text-accent mt-1" x-text="stat.value"></span>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="!selectedPlayer.stats || selectedPlayer.stats.length === 0">
                    <div class="bg-ink border border-line rounded-xl p-6 text-center text-body text-xs">
                        <p>{{ __('football.fixture.modal.stats_empty') }}</p>
                    </div>
                </template>
            </div>

            {{-- Modal Action Link to Full Player Profile --}}
            <div class="pt-2 flex items-center justify-end gap-3 relative z-10">
                <button @click="showModal = false" class="px-4 py-2.5 rounded-xl bg-ink text-white font-bold text-xs hover:bg-surface transition-colors">
                    {{ __('football.fixture.modal.close') }}
                </button>
                <a :href="'{{ url('/football/players') }}/' + selectedPlayer.player_id" 
                   class="px-5 py-2.5 rounded-xl bg-primary text-white font-semibold text-xs hover:bg-primary transition-colors flex items-center gap-1.5">
                    <span>{{ __('football.fixture.modal.full_profile') }}</span> &rarr;
                </a>
            </div>

        </div>
    </div>

    {{-- PREDIKSI HASIL (1X2) — dari proxy live, tidak dipersist --}}
    @php
        $ftPred = collect($predictions ?? [])->first(function ($p) {
            $dev = strtoupper($p['type']['developer_name'] ?? $p['type']['code'] ?? '');
            return str_contains($dev, 'FULLTIME_RESULT');
        });
        $pr = $ftPred['predictions'] ?? null;
        $pHome = isset($pr['home']) ? (float) $pr['home'] : null;
        $pDraw = isset($pr['draw']) ? (float) $pr['draw'] : null;
        $pAway = isset($pr['away']) ? (float) $pr['away'] : null;
    @endphp
    @if($pHome !== null && $pAway !== null)
        <div class="bg-surface border border-line rounded-xl p-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-line text-accent">
                    <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5"><path d="M4 15l5-5 3.5 3.5L20 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 6h5v5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div>
                    <span class="kicker block text-xs font-bold uppercase text-primary">{{ __('football.fixture.prediction_kicker') }}</span>
                    <h3 class="text-base font-bold text-white leading-tight">{{ __('football.fixture.prediction_heading') }}</h3>
                    <p class="text-xs text-body mt-0.5">{{ __('football.fixture.prediction_sub') }}</p>
                </div>
            </div>
            <div class="flex h-4 w-full overflow-hidden rounded-lg border border-line bg-ink">
                <div class="bg-primary h-full" style="width: {{ $pHome }}%" title="{{ __('football.fixture.win', ['team' => $home_team['name'] ?? __('football.card.home')]) }}"></div>
                <div class="bg-line h-full" style="width: {{ $pDraw ?? 0 }}%" title="{{ __('football.fixture.draw') }}"></div>
                <div class="bg-accent h-full" style="width: {{ $pAway }}%" title="{{ __('football.fixture.win', ['team' => $away_team['name'] ?? __('football.card.away')]) }}"></div>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs font-bold">
                <span class="text-accent">{{ $home_team['name'] ?? __('football.card.home') }} <span class="font-mono">{{ round($pHome) }}%</span></span>
                <span class="text-body">{{ __('football.fixture.draw') }} <span class="font-mono">{{ round($pDraw ?? 0) }}%</span></span>
                <span class="text-accent"><span class="font-mono">{{ round($pAway) }}%</span> {{ $away_team['name'] ?? __('football.card.away') }}</span>
            </div>
        </div>
    @endif

    {{-- ODDS PRA-LAGA (1X2) — dari proxy live --}}
    @php
        $oddsList = collect($odds ?? []);
        $mw = $oddsList->filter(function ($o) {
            $dev = strtoupper($o['market']['developer_name'] ?? $o['market']['name'] ?? '');
            return str_contains($dev, 'FULLTIME_RESULT') || str_contains($dev, 'MATCH_WINNER') || str_contains($dev, '3WAY') || str_contains($dev, '1X2');
        });
        // Take one bookmaker's line
        $bm = $mw->groupBy(fn ($o) => $o['bookmaker']['name'] ?? ($o['bookmaker_id'] ?? 'Bandar'))->first();
        $oHome = $oDraw = $oAway = null;
        $bookName = null;
        if ($bm) {
            $bookName = $bm[0]['bookmaker']['name'] ?? null;
            foreach ($bm as $o) {
                $lbl = strtolower($o['label'] ?? '');
                if (in_array($lbl, ['home', '1'])) $oHome = $o['value'] ?? null;
                elseif (in_array($lbl, ['draw', 'x'])) $oDraw = $o['value'] ?? null;
                elseif (in_array($lbl, ['away', '2'])) $oAway = $o['value'] ?? null;
            }
        }
    @endphp
    @if($oHome || $oAway)
        <div class="bg-surface border border-line rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-line text-accent">
                        <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.6"/><path d="M12 7v10M14.5 9.3c0-1.2-1.1-1.9-2.5-1.9s-2.5.7-2.5 1.8c0 2.5 5 1.3 5 3.9 0 1.2-1.1 2-2.5 2s-2.5-.8-2.5-2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </span>
                    <div>
                        <span class="kicker block text-xs font-bold uppercase text-primary">{{ __('football.fixture.odds_kicker') }}</span>
                        <h3 class="text-base font-bold text-white leading-tight">{{ __('football.fixture.odds_heading') }}</h3>
                    </div>
                </div>
                @if($bookName)
                    <span class="text-xs font-bold text-muted font-mono self-start">{{ $bookName }}</span>
                @endif
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div class="rounded-xl border border-line bg-ink p-4 text-center">
                    <span class="block text-xs uppercase font-bold text-muted truncate">{{ $home_team['name'] ?? '1' }}</span>
                    <span class="mt-1 block text-xl font-bold font-mono text-accent">{{ $oHome ?? '-' }}</span>
                </div>
                <div class="rounded-xl border border-line bg-ink p-4 text-center">
                    <span class="block text-xs uppercase font-bold text-muted">{{ __('football.fixture.draw') }}</span>
                    <span class="mt-1 block text-xl font-bold font-mono text-white">{{ $oDraw ?? '-' }}</span>
                </div>
                <div class="rounded-xl border border-line bg-ink p-4 text-center">
                    <span class="block text-xs uppercase font-bold text-muted truncate">{{ $away_team['name'] ?? '2' }}</span>
                    <span class="mt-1 block text-xl font-bold font-mono text-accent">{{ $oAway ?? '-' }}</span>
                </div>
            </div>
            <p class="mt-3 text-xs text-muted">{{ __('football.fixture.odds_note') }}</p>
        </div>
    @endif

    {{-- HEAD-TO-HEAD — riwayat pertemuan (proxy live) --}}
    @if(!empty($h2h))
        <div class="bg-surface border border-line rounded-xl p-6 space-y-4">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-line text-accent">
                    <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5"><path d="M14.5 4H20v5.5M20 4l-8.5 8.5M9.5 20H4v-5.5M4 20l8.5-8.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 14l5 5M9 14l-5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                </span>
                <div>
                    <span class="kicker block text-xs font-bold uppercase text-primary">{{ __('football.fixture.h2h_kicker') }}</span>
                    <h3 class="text-base font-bold text-white leading-tight">{{ __('football.fixture.h2h_heading') }}</h3>
                </div>
            </div>
            <div class="grid gap-3 md:grid-cols-2">
                @foreach(array_slice($h2h, 0, 6) as $f)
                    @include('football.partials.live-card', ['f' => $f])
                @endforeach
            </div>
        </div>
    @endif

</div>

<script>
    function matchCenter() {
        return {
            showModal: false,
            selectedPlayer: {},
            openModal(player, teamName, eventList = []) {
                this.selectedPlayer = Object.assign({}, player, { 
                    team_name: teamName,
                    events: eventList || []
                });
                this.showModal = true;
            }
        }
    }
</script>
@endsection
