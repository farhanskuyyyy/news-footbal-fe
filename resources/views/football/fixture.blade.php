@extends('layouts.app')

@section('title', ($fixture['name'] ?? 'Pertandingan') . ' - Match Center')

@section('content')
<div class="space-y-8" x-data="matchCenter()">

    {{-- Breadcrumb & Back --}}
    <div class="flex items-center justify-between">
        <a href="{{ url()->previous() ?? route('football.index') }}" class="group text-xs font-bold text-slate-400 hover:text-emerald-400 transition-colors flex items-center gap-2 bg-slate-900 border border-slate-800 hover:border-slate-700 px-3.5 py-2 rounded-xl">
            <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4 -ml-0.5 transition-transform group-hover:-translate-x-0.5">
                <path d="M14 7l-5 5 5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Kembali ke Jadwal &amp; Klasemen
        </a>
        <div class="flex items-center gap-2 text-xs text-slate-400 font-semibold">
            <span>{{ $league['name'] ?? 'Liga' }}</span>
            <span class="w-1 h-1 rounded-full bg-slate-700"></span>
            <span class="text-emerald-400">{{ $season['name'] ?? 'Musim' }}</span>
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
        $isLive = in_array($stateCode, ['LIVE', '1H', '2H', 'HT', 'ET']);

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
                    $playerEventsMap[$pId]['list'][] = "⚽ Gol ({$min}')";
                }
                if ($relPId) {
                    $playerEventsMap[$relPId]['assists'] = ($playerEventsMap[$relPId]['assists'] ?? 0) + 1;
                    $playerEventsMap[$relPId]['list'][] = "👟 Assist ({$min}')";
                }
            }
            // 2. Substitution
            elseif ($typeId == 18 || str_contains($typeName, 'sub')) {
                if ($pId) {
                    $playerEventsMap[$pId]['sub_in'] = $min;
                    $playerEventsMap[$pId]['list'][] = "🟢 Masuk ({$min}')";
                }
                if ($relPId) {
                    $playerEventsMap[$relPId]['sub_out'] = $min;
                    $playerEventsMap[$relPId]['list'][] = "🔴 Keluar ({$min}')";
                }
            }
            // 3. Yellow Card
            elseif ($typeId == 19 || str_contains($typeName, 'yellow') || str_contains($typeName, 'kuning')) {
                if ($pId) {
                    $playerEventsMap[$pId]['yellow_cards'] = ($playerEventsMap[$pId]['yellow_cards'] ?? 0) + 1;
                    $playerEventsMap[$pId]['list'][] = "🟨 Kartu Kuning ({$min}')";
                }
            }
            // 4. Red Card / Yellow-Red
            elseif ($typeId == 20 || $typeId == 21 || str_contains($typeName, 'red') || str_contains($typeName, 'merah')) {
                if ($pId) {
                    $playerEventsMap[$pId]['red_cards'] = ($playerEventsMap[$pId]['red_cards'] ?? 0) + 1;
                    $playerEventsMap[$pId]['list'][] = "🟥 Kartu Merah ({$min}')";
                }
            }
        }
    @endphp

    {{-- BROADCAST SCOREBOARD HERO --}}
    <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 rounded-3xl shadow-2xl p-6 sm:p-10 text-white border border-slate-800 relative overflow-hidden">
        {{-- Ambient Backlight --}}
        <div class="absolute inset-0 bg-radial from-emerald-500/10 via-transparent to-transparent pointer-events-none"></div>

        <div class="relative z-10">
            {{-- Top State & Kickoff --}}
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-2">
                    @if($stateCode)
                        <span class="inline-flex items-center gap-1.5 font-mono font-black text-xs px-4 py-1.5 rounded-full uppercase tracking-wider shadow-lg
                            {{ $isLive ? 'bg-red-600 text-white animate-pulse ring-2 ring-red-400/50' : '' }}
                            {{ in_array($stateCode, ['FT', 'AET', 'FT_PEN']) ? 'bg-slate-800 text-slate-200 border border-slate-700' : '' }}
                            {{ in_array($stateCode, ['NS', 'TBA']) ? 'bg-blue-950 text-blue-300 border border-blue-800' : '' }}
                            {{ !in_array($stateCode, ['LIVE', '1H', '2H', 'HT', 'ET', 'FT', 'AET', 'FT_PEN', 'NS', 'TBA']) ? 'bg-slate-800 text-slate-300' : '' }}
                        ">
                            <span class="w-2 h-2 rounded-full {{ $isLive ? 'bg-white' : 'bg-emerald-400' }}"></span>
                            {{ $stateCode }} • {{ $stateName }}
                        </span>
                    @else
                        <span class="inline-block bg-slate-800 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider text-slate-300">
                            {{ $fixture['result_info'] ?? 'Pertandingan' }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-400 mt-2.5 font-medium">
                    {{ $fixture['starting_at'] ? \Illuminate\Support\Carbon::parse($fixture['starting_at'], 'UTC')->setTimezone('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y • H:i') . ' WIB' : 'Jadwal Ditentukan' }}
                </p>
            </div>

            {{-- Teams & Score Display --}}
            <div class="flex flex-col sm:flex-row items-center justify-around gap-8 text-center">
                {{-- Home Team --}}
                <div class="flex-1 flex flex-col items-center gap-3">
                    @if(!empty($homeTeam['id']))
                        <a href="{{ route('football.team', $homeTeam['id']) }}" class="group block">
                            @if(!empty($homeTeam['image_path']))
                                <img src="{{ $homeTeam['image_path'] }}" alt="{{ $homeName }}" class="w-20 h-20 sm:w-24 sm:h-24 object-contain filter drop-shadow-xl group-hover:scale-110 transition-transform mx-auto">
                            @else
                                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-slate-800 flex items-center justify-center text-emerald-400 mx-auto shadow-inner">
                                    <svg viewBox="0 0 24 24" fill="none" class="w-10 h-10"><path d="M12 3l7 2.4v5.4c0 4.5-3 8.2-7 9.5-4-1.3-7-5-7-9.5V5.4L12 3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                                </div>
                            @endif
                            <h2 class="text-xl sm:text-2xl font-black tracking-tight mt-3 text-white group-hover:text-emerald-400 transition-colors">{{ $homeName }}</h2>
                        </a>
                    @else
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-slate-800 flex items-center justify-center text-emerald-400 mx-auto shadow-inner">
                            <svg viewBox="0 0 24 24" fill="none" class="w-10 h-10"><path d="M12 3l7 2.4v5.4c0 4.5-3 8.2-7 9.5-4-1.3-7-5-7-9.5V5.4L12 3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black tracking-tight mt-3 text-white">{{ $homeName }}</h2>
                    @endif
                    <span class="text-[11px] text-emerald-400 font-bold uppercase tracking-wider bg-emerald-950/60 px-3 py-0.5 rounded-full border border-emerald-800/40">Tuan Rumah</span>
                </div>

                {{-- Center Scoreboard Box --}}
                <div class="bg-slate-950/90 backdrop-blur-md px-10 py-5 rounded-3xl border border-slate-800 shadow-2xl flex items-center gap-6">
                    <span class="text-6xl sm:text-7xl font-black font-mono tracking-tight text-white">{{ $homeGoals }}</span>
                    <span class="text-3xl text-slate-600 font-black">:</span>
                    <span class="text-6xl sm:text-7xl font-black font-mono tracking-tight text-white">{{ $awayGoals }}</span>
                </div>

                {{-- Away Team --}}
                <div class="flex-1 flex flex-col items-center gap-3">
                    @if(!empty($awayTeam['id']))
                        <a href="{{ route('football.team', $awayTeam['id']) }}" class="group block">
                            @if(!empty($awayTeam['image_path']))
                                <img src="{{ $awayTeam['image_path'] }}" alt="{{ $awayName }}" class="w-20 h-20 sm:w-24 sm:h-24 object-contain filter drop-shadow-xl group-hover:scale-110 transition-transform mx-auto">
                            @else
                                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-slate-800 flex items-center justify-center text-blue-400 mx-auto shadow-inner">
                                    <svg viewBox="0 0 24 24" fill="none" class="w-10 h-10"><path d="M12 3l7 2.4v5.4c0 4.5-3 8.2-7 9.5-4-1.3-7-5-7-9.5V5.4L12 3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                                </div>
                            @endif
                            <h2 class="text-xl sm:text-2xl font-black tracking-tight mt-3 text-white group-hover:text-emerald-400 transition-colors">{{ $awayName }}</h2>
                        </a>
                    @else
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-slate-800 flex items-center justify-center text-blue-400 mx-auto shadow-inner">
                            <svg viewBox="0 0 24 24" fill="none" class="w-10 h-10"><path d="M12 3l7 2.4v5.4c0 4.5-3 8.2-7 9.5-4-1.3-7-5-7-9.5V5.4L12 3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black tracking-tight mt-3 text-white">{{ $awayName }}</h2>
                    @endif
                    <span class="text-[11px] text-blue-400 font-bold uppercase tracking-wider bg-blue-950/60 px-3 py-0.5 rounded-full border border-blue-800/40">Tim Tamu</span>
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
                    $nm   = $ev['player_name'] ?? 'Pemain';
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
                        $line = ['icon' => '❌', 'name' => $nm, 'min' => $fmtMin($ev), 'note' => 'Penalti gagal', 'cls' => 'text-red-400', 'sort' => (int)($ev['minute'] ?? 0)];
                    } elseif ($isGoal) {
                        $tag = $isOwn ? 'OG' : ($isPen ? 'P' : '');
                        $line = ['icon' => '⚽', 'name' => $nm, 'min' => $fmtMin($ev), 'note' => $tag, 'cls' => 'text-white', 'sort' => (int)($ev['minute'] ?? 0)];
                        $side = $isOwn ? !$isHome : $isHome; // own goals credited to the opponent
                    } elseif ($isRed) {
                        $line = ['icon' => '🟥', 'name' => $nm, 'min' => $fmtMin($ev), 'note' => '', 'cls' => 'text-red-400', 'sort' => (int)($ev['minute'] ?? 0)];
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
                                @if($g['note'])<span class="text-[10px] text-slate-500 font-bold">({{ $g['note'] }})</span>@endif
                                <span class="font-mono text-slate-400 shrink-0">{{ $g['min'] }}</span>
                                <span class="shrink-0">{{ $g['icon'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="space-y-1.5">
                        @foreach($awayScorers as $g)
                            <div class="flex items-center gap-2 text-xs sm:text-sm">
                                <span class="shrink-0">{{ $g['icon'] }}</span>
                                <span class="font-mono text-slate-400 shrink-0">{{ $g['min'] }}</span>
                                @if($g['note'])<span class="text-[10px] text-slate-500 font-bold">({{ $g['note'] }})</span>@endif
                                <span class="{{ $g['cls'] }} font-semibold truncate">{{ $g['name'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Match Meta (Venue & Referee) --}}
            @if($venue || count($referees) > 0)
                <div class="mt-10 pt-5 border-t border-slate-800/80 flex flex-wrap items-center justify-center gap-x-8 gap-y-3 text-xs text-slate-400 font-medium">
                    @if($venue)
                        <span class="flex items-center gap-2">
                            <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4 text-emerald-400 shrink-0"><path d="M12 21s6-5.3 6-10a6 6 0 10-12 0c0 4.7 6 10 6 10z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="11" r="2.2" stroke="currentColor" stroke-width="1.6"/></svg>
                            <span><strong class="text-slate-200">Stadion:</strong> {{ $venue['name'] }} ({{ $venue['city_name'] ?? '' }})</span>
                        </span>
                    @endif
                    @if(count($referees) > 0)
                        <span class="flex items-center gap-2">
                            <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4 text-emerald-400 shrink-0"><circle cx="10" cy="13.5" r="4.5" stroke="currentColor" stroke-width="1.6"/><path d="M14.2 11.4H21v2.3a1 1 0 01-1 1h-3.2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 9V6.2h3.4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span><strong class="text-slate-200">Wasit:</strong> {{ $referees[0]['name'] ?? 'Wasit Pertandingan' }}</span>
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
            <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                            <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5"><rect x="3.5" y="4.5" width="17" height="15" rx="1.5" stroke="currentColor" stroke-width="1.6"/><path d="M12 4.5v15M3.5 9.5H6M3.5 14.5H6M20.5 9.5H18M20.5 14.5H18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="12" cy="12" r="2.4" stroke="currentColor" stroke-width="1.6"/></svg>
                        </span>
                        <div>
                            <span class="kicker block text-[10px] font-bold uppercase text-emerald-400">Susunan Pemain</span>
                            <h3 class="text-lg font-black text-white leading-tight">Formasi 22 Pemain di Lapangan</h3>
                        </div>
                    </div>

                    {{-- Formations Indicator --}}
                    <div class="flex items-center gap-3 text-xs font-bold">
                        <span class="px-3 py-1.5 rounded-xl bg-emerald-950 text-emerald-300 border border-emerald-800 flex items-center gap-1.5">
                            <svg viewBox="0 0 24 24" fill="none" class="w-3.5 h-3.5 shrink-0"><path d="M12 3l7 2.4v5.4c0 4.5-3 8.2-7 9.5-4-1.3-7-5-7-9.5V5.4L12 3z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                            <span class="truncate max-w-[110px]">{{ $homeName }}</span>
                            <span class="font-mono font-black">{{ $homeFormation }}</span>
                        </span>
                        <span class="px-3 py-1.5 rounded-xl bg-blue-950 text-blue-300 border border-blue-800 flex items-center gap-1.5">
                            <svg viewBox="0 0 24 24" fill="none" class="w-3.5 h-3.5 shrink-0"><path d="M12 3l7 2.4v5.4c0 4.5-3 8.2-7 9.5-4-1.3-7-5-7-9.5V5.4L12 3z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                            <span class="truncate max-w-[110px]">{{ $awayName }}</span>
                            <span class="font-mono font-black">{{ $awayFormation }}</span>
                        </span>
                    </div>
                </div>

                {{-- FULL PITCH CONTAINER (22 PLAYERS LIVE COMBAT) --}}
                <div class="relative w-full rounded-3xl p-6 shadow-2xl football-pitch-bg border-4 border-emerald-950/90 overflow-hidden min-h-[820px] flex flex-col justify-between">
                    
                    {{-- FIELD MARKINGS (TOP & BOTTOM PENALTY BOXES + CENTER CIRCLE) --}}
                    {{-- Top Goal Area & Box (Home) --}}
                    <div class="absolute inset-x-12 top-0 h-28 border-b-2 border-x-2 border-white/20 rounded-b-2xl pointer-events-none"></div>
                    <div class="absolute inset-x-24 top-0 h-12 border-b-2 border-x-2 border-white/20 rounded-b-lg pointer-events-none"></div>

                    {{-- Center Circle & Halfway Line --}}
                    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-36 h-36 rounded-full border-2 border-white/20 pointer-events-none"></div>
                    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-2 h-2 rounded-full bg-white/40 pointer-events-none"></div>
                    <div class="absolute inset-x-0 top-1/2 -translate-y-1/2 h-0.5 bg-white/20 pointer-events-none"></div>

                    {{-- Bottom Goal Area & Box (Away) --}}
                    <div class="absolute inset-x-12 bottom-0 h-28 border-t-2 border-x-2 border-white/20 rounded-t-2xl pointer-events-none"></div>
                    <div class="absolute inset-x-24 bottom-0 h-12 border-t-2 border-x-2 border-white/20 rounded-t-lg pointer-events-none"></div>

                    {{-- TOP HALF: HOME TEAM STARTING XI --}}
                    <div class="relative z-10 space-y-6 pb-6 border-b border-white/10">
                        <div class="text-center">
                            <span class="inline-flex items-center gap-2 text-[10px] font-mono font-black uppercase tracking-widest text-emerald-300 bg-slate-950/80 px-3 py-1 rounded-full border border-emerald-500/30 shadow-md">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_8px] shadow-emerald-400/60"></span>
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
                                                    @click="openModal({{ json_encode($p) }}, '{{ addslashes($homeName) }}', {{ json_encode($pEventsList) }})"
                                                    class="group flex flex-col items-center text-center transition-transform hover:scale-115 focus:outline-none relative">
                                                
                                                <div class="relative">
                                                    @if(!empty($p['player_image']))
                                                        <img src="{{ $p['player_image'] }}" alt="{{ $p['player_name'] }}" class="w-12 h-12 rounded-full object-cover border-2 border-emerald-400 bg-slate-900 shadow-xl group-hover:ring-4 group-hover:ring-emerald-400/50 transition-all">
                                                    @else
                                                        <div class="w-12 h-12 rounded-full bg-slate-900 border-2 border-emerald-400 text-emerald-400 flex items-center justify-center font-bold text-xs shadow-xl">
                                                            {{ $p['jersey_number'] ?? '#' }}
                                                        </div>
                                                    @endif

                                                    {{-- Jersey Number Badge --}}
                                                    <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-slate-950 text-emerald-400 font-mono font-black text-[10px] flex items-center justify-center border border-emerald-500 shadow-md">
                                                        {{ $p['jersey_number'] ?? '•' }}
                                                    </span>

                                                    {{-- Rating Badge (FixtureLineupDetail type_id 118) --}}
                                                    @if(!empty($p['rating']))
                                                        @php
                                                            $rt = (float) $p['rating'];
                                                            $rtCls = $rt >= 7 ? 'bg-emerald-500 text-slate-950' : ($rt >= 6 ? 'bg-amber-400 text-slate-950' : 'bg-red-500 text-white');
                                                        @endphp
                                                        <span class="absolute -bottom-1 -left-1 h-5 min-w-[22px] px-1 rounded-md {{ $rtCls }} font-mono font-black text-[10px] flex items-center justify-center border border-slate-950 shadow-md z-20" title="Rating {{ number_format($rt, 2) }}">
                                                            {{ number_format($rt, 1) }}
                                                        </span>
                                                    @endif

                                                    {{-- EVENT BADGES OVERLAY (GOAL, ASSIST, CARD, SUB) --}}
                                                    @if($evStats)
                                                        {{-- Top-Right: Goal & Assist Badges --}}
                                                        <div class="absolute -top-2.5 -right-2.5 flex items-center gap-0.5 z-20">
                                                            @if(!empty($evStats['goals']))
                                                                <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] px-1 rounded-full bg-emerald-500 text-slate-950 font-black text-[10px] shadow-lg ring-2 ring-slate-950 animate-bounce" title="Gol ({{ $evStats['goals'] }})">
                                                                    ⚽{{ $evStats['goals'] > 1 ? $evStats['goals'] : '' }}
                                                                </span>
                                                            @endif
                                                            @if(!empty($evStats['assists']))
                                                                <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] px-1 rounded-full bg-teal-400 text-slate-950 font-black text-[10px] shadow-lg ring-2 ring-slate-950" title="Assist ({{ $evStats['assists'] }})">
                                                                    👟{{ $evStats['assists'] > 1 ? $evStats['assists'] : '' }}
                                                                </span>
                                                            @endif
                                                        </div>

                                                        {{-- Top-Left: Cards & Sub Badges --}}
                                                        <div class="absolute -top-2.5 -left-2.5 flex items-center gap-0.5 z-20">
                                                            @if(!empty($evStats['red_cards']))
                                                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] rounded bg-red-600 text-white font-black text-[9px] shadow-lg ring-2 ring-slate-950" title="Kartu Merah">
                                                                    🟥
                                                                </span>
                                                            @elseif(!empty($evStats['yellow_cards']))
                                                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] rounded bg-yellow-400 text-slate-950 font-black text-[9px] shadow-lg ring-2 ring-slate-950" title="Kartu Kuning ({{ $evStats['yellow_cards'] }})">
                                                                    🟨{{ $evStats['yellow_cards'] > 1 ? '2' : '' }}
                                                                </span>
                                                            @endif
                                                            @if(!empty($evStats['sub_out']))
                                                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] rounded-full bg-red-600 text-white font-bold text-[9px] shadow-lg ring-2 ring-slate-950" title="Keluar menit {{ $evStats['sub_out'] }}'">
                                                                    🔴
                                                                </span>
                                                            @elseif(!empty($evStats['sub_in']))
                                                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] rounded-full bg-emerald-500 text-slate-950 font-bold text-[9px] shadow-lg ring-2 ring-slate-950" title="Masuk menit {{ $evStats['sub_in'] }}'">
                                                                    🟢
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>

                                                <span class="mt-1 px-2 py-0.5 rounded-md bg-slate-950/90 text-white font-bold text-[10px] shadow-md border border-slate-800/80 group-hover:bg-emerald-500 group-hover:text-slate-950 transition-colors max-w-[80px] truncate">
                                                    {{ $p['player_name'] ?: 'Pemain #' . $p['player_id'] }}
                                                </span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-300 text-center py-4">Lineup Home belum tersedia.</p>
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
                                                    @click="openModal({{ json_encode($p) }}, '{{ addslashes($awayName) }}', {{ json_encode($pEventsList) }})"
                                                    class="group flex flex-col items-center text-center transition-transform hover:scale-115 focus:outline-none relative">
                                                
                                                <div class="relative">
                                                    @if(!empty($p['player_image']))
                                                        <img src="{{ $p['player_image'] }}" alt="{{ $p['player_name'] }}" class="w-12 h-12 rounded-full object-cover border-2 border-blue-400 bg-slate-900 shadow-xl group-hover:ring-4 group-hover:ring-blue-400/50 transition-all">
                                                    @else
                                                        <div class="w-12 h-12 rounded-full bg-slate-900 border-2 border-blue-400 text-blue-400 flex items-center justify-center font-bold text-xs shadow-xl">
                                                            {{ $p['jersey_number'] ?? '#' }}
                                                        </div>
                                                    @endif

                                                    {{-- Jersey Number Badge --}}
                                                    <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-slate-950 text-blue-400 font-mono font-black text-[10px] flex items-center justify-center border border-blue-500 shadow-md">
                                                        {{ $p['jersey_number'] ?? '•' }}
                                                    </span>

                                                    {{-- Rating Badge (FixtureLineupDetail type_id 118) --}}
                                                    @if(!empty($p['rating']))
                                                        @php
                                                            $rt = (float) $p['rating'];
                                                            $rtCls = $rt >= 7 ? 'bg-emerald-500 text-slate-950' : ($rt >= 6 ? 'bg-amber-400 text-slate-950' : 'bg-red-500 text-white');
                                                        @endphp
                                                        <span class="absolute -bottom-1 -left-1 h-5 min-w-[22px] px-1 rounded-md {{ $rtCls }} font-mono font-black text-[10px] flex items-center justify-center border border-slate-950 shadow-md z-20" title="Rating {{ number_format($rt, 2) }}">
                                                            {{ number_format($rt, 1) }}
                                                        </span>
                                                    @endif

                                                    {{-- EVENT BADGES OVERLAY (GOAL, ASSIST, CARD, SUB) --}}
                                                    @if($evStats)
                                                        {{-- Top-Right: Goal & Assist Badges --}}
                                                        <div class="absolute -top-2.5 -right-2.5 flex items-center gap-0.5 z-20">
                                                            @if(!empty($evStats['goals']))
                                                                <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] px-1 rounded-full bg-emerald-500 text-slate-950 font-black text-[10px] shadow-lg ring-2 ring-slate-950 animate-bounce" title="Gol ({{ $evStats['goals'] }})">
                                                                    ⚽{{ $evStats['goals'] > 1 ? $evStats['goals'] : '' }}
                                                                </span>
                                                            @endif
                                                            @if(!empty($evStats['assists']))
                                                                <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] px-1 rounded-full bg-teal-400 text-slate-950 font-black text-[10px] shadow-lg ring-2 ring-slate-950" title="Assist ({{ $evStats['assists'] }})">
                                                                    👟{{ $evStats['assists'] > 1 ? $evStats['assists'] : '' }}
                                                                </span>
                                                            @endif
                                                        </div>

                                                        {{-- Top-Left: Cards & Sub Badges --}}
                                                        <div class="absolute -top-2.5 -left-2.5 flex items-center gap-0.5 z-20">
                                                            @if(!empty($evStats['red_cards']))
                                                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] rounded bg-red-600 text-white font-black text-[9px] shadow-lg ring-2 ring-slate-950" title="Kartu Merah">
                                                                    🟥
                                                                </span>
                                                            @elseif(!empty($evStats['yellow_cards']))
                                                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] rounded bg-yellow-400 text-slate-950 font-black text-[9px] shadow-lg ring-2 ring-slate-950" title="Kartu Kuning ({{ $evStats['yellow_cards'] }})">
                                                                    🟨{{ $evStats['yellow_cards'] > 1 ? '2' : '' }}
                                                                </span>
                                                            @endif
                                                            @if(!empty($evStats['sub_out']))
                                                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] rounded-full bg-red-600 text-white font-bold text-[9px] shadow-lg ring-2 ring-slate-950" title="Keluar menit {{ $evStats['sub_out'] }}'">
                                                                    🔴
                                                                </span>
                                                            @elseif(!empty($evStats['sub_in']))
                                                                <span class="inline-flex items-center justify-center w-[18px] h-[18px] rounded-full bg-emerald-500 text-slate-950 font-bold text-[9px] shadow-lg ring-2 ring-slate-950" title="Masuk menit {{ $evStats['sub_in'] }}'">
                                                                    🟢
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>

                                                <span class="mt-1 px-2 py-0.5 rounded-md bg-slate-950/90 text-white font-bold text-[10px] shadow-md border border-slate-800/80 group-hover:bg-blue-500 group-hover:text-slate-950 transition-colors max-w-[80px] truncate">
                                                    {{ $p['player_name'] ?: 'Pemain #' . $p['player_id'] }}
                                                </span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-300 text-center py-4">Lineup Away belum tersedia.</p>
                        @endif

                        <div class="text-center pt-2">
                            <span class="inline-flex items-center gap-2 text-[10px] font-mono font-black uppercase tracking-widest text-blue-300 bg-slate-950/80 px-3 py-1 rounded-full border border-blue-500/30 shadow-md">
                                <span class="w-2 h-2 rounded-full bg-blue-400 shadow-[0_0_8px] shadow-blue-400/60"></span>
                                {{ $awayName }} ({{ $awayFormation }})
                            </span>
                        </div>
                    </div>

                </div>

                {{-- BOTH TEAMS BENCH LISTS --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                    {{-- Home Bench --}}
                    <div class="bg-slate-950/80 border border-slate-800 rounded-2xl p-4 space-y-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-400 flex items-center gap-2">
                            <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4 shrink-0"><path d="M4 8.5h13l-3-3M20 15.5H7l3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Cadangan {{ $homeName }}
                        </span>
                        <div class="grid grid-cols-2 gap-2">
                            @forelse($homeBench as $p)
                                @php
                                    $pId = $p['player_id'] ?? $p['id'];
                                    $evStats = $playerEventsMap[$pId] ?? null;
                                    $pEventsList = $evStats['list'] ?? [];
                                @endphp
                                <button type="button" 
                                        @click="openModal({{ json_encode($p) }}, '{{ addslashes($homeName) }}', {{ json_encode($pEventsList) }})"
                                        class="flex items-center gap-2 p-2 rounded-xl bg-slate-900 hover:bg-slate-850 border border-slate-800 text-left transition-colors relative">
                                    @if(!empty($p['player_image']))
                                        <img src="{{ $p['player_image'] }}" alt="{{ $p['player_name'] }}" class="w-7 h-7 rounded-full object-cover border border-slate-700 flex-shrink-0">
                                    @else
                                        <div class="w-7 h-7 rounded-full bg-slate-800 text-slate-400 font-mono font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                                            {{ $p['jersey_number'] ?? '•' }}
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <span class="text-[11px] font-bold text-slate-200 block truncate hover:text-emerald-400">{{ $p['player_name'] }}</span>
                                        <div class="flex items-center gap-1">
                                            <span class="text-[9px] text-slate-500 font-mono">No. {{ $p['jersey_number'] ?? '-' }}</span>
                                            @if(!empty($p['rating']))
                                                @php
                                                    $rt = (float) $p['rating'];
                                                    $rtCls = $rt >= 7 ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : ($rt >= 6 ? 'bg-amber-400/20 text-amber-300 border-amber-400/30' : 'bg-red-500/20 text-red-300 border-red-500/30');
                                                @endphp
                                                <span class="text-[9px] font-mono font-black px-1 rounded border {{ $rtCls }}" title="Rating {{ number_format($rt, 2) }}">{{ number_format($rt, 1) }}</span>
                                            @endif
                                            @if($evStats)
                                                @if(!empty($evStats['goals']))
                                                    <span class="text-[10px]">⚽</span>
                                                @endif
                                                @if(!empty($evStats['sub_in']))
                                                    <span class="text-[10px]">🟢</span>
                                                @endif
                                                @if(!empty($evStats['yellow_cards']))
                                                    <span class="text-[10px]">🟨</span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </button>
                            @empty
                                <p class="text-xs text-slate-500 col-span-2 italic">Tidak ada cadangan.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Away Bench --}}
                    <div class="bg-slate-950/80 border border-slate-800 rounded-2xl p-4 space-y-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-blue-400 flex items-center gap-2">
                            <svg viewBox="0 0 24 24" fill="none" class="w-4 h-4 shrink-0"><path d="M4 8.5h13l-3-3M20 15.5H7l3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Cadangan {{ $awayName }}
                        </span>
                        <div class="grid grid-cols-2 gap-2">
                            @forelse($awayBench as $p)
                                @php
                                    $pId = $p['player_id'] ?? $p['id'];
                                    $evStats = $playerEventsMap[$pId] ?? null;
                                    $pEventsList = $evStats['list'] ?? [];
                                @endphp
                                <button type="button" 
                                        @click="openModal({{ json_encode($p) }}, '{{ addslashes($awayName) }}', {{ json_encode($pEventsList) }})"
                                        class="flex items-center gap-2 p-2 rounded-xl bg-slate-900 hover:bg-slate-850 border border-slate-800 text-left transition-colors relative">
                                    @if(!empty($p['player_image']))
                                        <img src="{{ $p['player_image'] }}" alt="{{ $p['player_name'] }}" class="w-7 h-7 rounded-full object-cover border border-slate-700 flex-shrink-0">
                                    @else
                                        <div class="w-7 h-7 rounded-full bg-slate-800 text-slate-400 font-mono font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                                            {{ $p['jersey_number'] ?? '•' }}
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <span class="text-[11px] font-bold text-slate-200 block truncate hover:text-blue-400">{{ $p['player_name'] }}</span>
                                        <div class="flex items-center gap-1">
                                            <span class="text-[9px] text-slate-500 font-mono">No. {{ $p['jersey_number'] ?? '-' }}</span>
                                            @if(!empty($p['rating']))
                                                @php
                                                    $rt = (float) $p['rating'];
                                                    $rtCls = $rt >= 7 ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' : ($rt >= 6 ? 'bg-amber-400/20 text-amber-300 border-amber-400/30' : 'bg-red-500/20 text-red-300 border-red-500/30');
                                                @endphp
                                                <span class="text-[9px] font-mono font-black px-1 rounded border {{ $rtCls }}" title="Rating {{ number_format($rt, 2) }}">{{ number_format($rt, 1) }}</span>
                                            @endif
                                            @if($evStats)
                                                @if(!empty($evStats['goals']))
                                                    <span class="text-[10px]">⚽</span>
                                                @endif
                                                @if(!empty($evStats['sub_in']))
                                                    <span class="text-[10px]">🟢</span>
                                                @endif
                                                @if(!empty($evStats['yellow_cards']))
                                                    <span class="text-[10px]">🟨</span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </button>
                            @empty
                                <p class="text-xs text-slate-500 col-span-2 italic">Tidak ada cadangan.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>

            {{-- SECTION 2: MATCH TIMELINE EVENTS (HOME VS AWAY) --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                            <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.6"/><path d="M12 7.5V12l3 1.7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <span class="kicker block text-[10px] font-bold uppercase text-emerald-400">Timeline</span>
                            <h3 class="text-lg font-black text-white leading-tight">Garis Waktu Pertandingan</h3>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 text-xs font-bold">
                        <span class="inline-flex items-center gap-1.5 text-emerald-400">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>{{ $homeName }}
                        </span>
                        <span class="text-slate-700">/</span>
                        <span class="inline-flex items-center gap-1.5 text-blue-400">
                            {{ $awayName }}<span class="w-2 h-2 rounded-full bg-blue-400"></span>
                        </span>
                    </div>
                </div>

                @if(count($events) > 0)
                    <div class="space-y-4 relative">
                        {{-- Center timeline vertical beam --}}
                        <div class="hidden sm:block absolute left-1/2 top-0 bottom-0 w-0.5 bg-slate-800 -translate-x-1/2"></div>

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
                                        <div class="bg-slate-950/90 border border-slate-800 hover:border-emerald-500/50 rounded-2xl p-4 shadow-lg text-left transition-all">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="space-y-2 flex-1">
                                                    <div class="flex items-center gap-2 text-xs font-extrabold text-emerald-400 uppercase tracking-wider">
                                                        @if($isGoal) ⚽ GOL
                                                        @elseif($isSub) 🔄 PERGANTIAN PEMAIN
                                                        @elseif($ev['type_id'] == 19) 🟨 KARTU KUNING
                                                        @elseif($ev['type_id'] == 20) 🟥 KARTU MERAH
                                                        @else 📌 {{ $ev['event_type_name'] ?? 'EVENT' }}
                                                        @endif
                                                    </div>

                                                    {{-- Substitution Player Face Cards --}}
                                                    @if($isSub)
                                                        <div class="space-y-2 pt-1">
                                                            <div class="flex items-center gap-2.5">
                                                                @if(!empty($ev['player_image']))
                                                                    <img src="{{ $ev['player_image'] }}" alt="{{ $ev['player_name'] }}" class="w-8 h-8 rounded-full object-cover border-2 border-emerald-500 flex-shrink-0">
                                                                @else
                                                                    <div class="w-8 h-8 rounded-full bg-slate-800 text-emerald-400 text-xs font-bold flex items-center justify-center flex-shrink-0">🟢</div>
                                                                @endif
                                                                <div>
                                                                    <span class="text-[10px] font-black text-emerald-400 uppercase">MASUK:</span>
                                                                    <span class="text-xs font-bold text-white block truncate">{{ $ev['player_name'] ?: 'Pemain Masuk' }}</span>
                                                                </div>
                                                            </div>
                                                            @if(!empty($ev['related_player_name']))
                                                                <div class="flex items-center gap-2.5 opacity-75">
                                                                    @if(!empty($ev['related_player_image']))
                                                                        <img src="{{ $ev['related_player_image'] }}" alt="{{ $ev['related_player_name'] }}" class="w-7 h-7 rounded-full object-cover border border-red-500 flex-shrink-0">
                                                                    @else
                                                                        <div class="w-7 h-7 rounded-full bg-slate-800 text-red-400 text-xs font-bold flex items-center justify-center flex-shrink-0">🔴</div>
                                                                    @endif
                                                                    <div>
                                                                        <span class="text-[9px] font-bold text-red-400 uppercase">KELUAR:</span>
                                                                        <span class="text-[11px] font-semibold text-slate-300 block truncate">{{ $ev['related_player_name'] }}</span>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    {{-- Goal Scorer & Assist --}}
                                                    @elseif($isGoal)
                                                        <div class="flex items-center gap-3 pt-1">
                                                            @if(!empty($ev['player_image']))
                                                                <img src="{{ $ev['player_image'] }}" alt="{{ $ev['player_name'] }}" class="w-10 h-10 rounded-full object-cover border-2 border-emerald-400 shadow-md flex-shrink-0">
                                                            @else
                                                                <div class="w-10 h-10 rounded-full bg-slate-800 text-white flex items-center justify-center text-base flex-shrink-0">⚽</div>
                                                            @endif
                                                            <div>
                                                                <span class="text-sm font-extrabold text-white block">{{ $ev['player_name'] ?: 'Pencetak Gol' }}</span>
                                                                @if(!empty($ev['related_player_name']))
                                                                    <p class="text-[11px] text-slate-400 mt-0.5">
                                                                        👟 Assist: <strong class="text-slate-200">{{ $ev['related_player_name'] }}</strong>
                                                                    </p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    {{-- Cards --}}
                                                    @else
                                                        <span class="text-xs font-bold text-white block">{{ $ev['player_name'] ?: 'Pemain #' . ($ev['player_id'] ?? '') }}</span>
                                                    @endif

                                                    @if(!empty($ev['info']))
                                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold bg-slate-900 text-slate-300 px-2.5 py-0.5 rounded-full border border-slate-800 mt-1">
                                                            <svg viewBox="0 0 24 24" fill="none" class="w-3 h-3 text-slate-400 shrink-0"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.6"/><path d="M12 11v5M12 8h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                                            {{ $ev['info'] }}
                                                        </span>
                                                    @endif
                                                </div>

                                                @if(!empty($ev['result']))
                                                    <span class="font-mono font-black text-xs bg-emerald-500 text-slate-950 px-2.5 py-1 rounded-lg shadow-sm">
                                                        {{ $ev['result'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- CENTER MINUTE BADGE --}}
                                <div class="z-10 flex-shrink-0 mx-2 hidden sm:flex items-center justify-center w-9 h-9 rounded-full bg-slate-950 text-white font-mono font-black text-xs border-2 border-slate-700 shadow-xl">
                                    {{ $ev['minute'] ?? 0 }}'
                                </div>

                                {{-- AWAY EVENT (RIGHT) --}}
                                <div class="w-full sm:w-[45%] {{ !$isHome ? 'block' : 'hidden sm:block sm:invisible' }}">
                                    @if(!$isHome)
                                        <div class="bg-slate-950/90 border border-slate-800 hover:border-blue-500/50 rounded-2xl p-4 shadow-lg text-left transition-all">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="space-y-2 flex-1">
                                                    <div class="flex items-center gap-2 text-xs font-extrabold text-blue-400 uppercase tracking-wider">
                                                        @if($isGoal) ⚽ GOL
                                                        @elseif($isSub) 🔄 PERGANTIAN PEMAIN
                                                        @elseif($ev['type_id'] == 19) 🟨 KARTU KUNING
                                                        @elseif($ev['type_id'] == 20) 🟥 KARTU MERAH
                                                        @else 📌 {{ $ev['event_type_name'] ?? 'EVENT' }}
                                                        @endif
                                                    </div>

                                                    {{-- Substitution Player Face Cards --}}
                                                    @if($isSub)
                                                        <div class="space-y-2 pt-1">
                                                            <div class="flex items-center gap-2.5">
                                                                @if(!empty($ev['player_image']))
                                                                    <img src="{{ $ev['player_image'] }}" alt="{{ $ev['player_name'] }}" class="w-8 h-8 rounded-full object-cover border-2 border-emerald-500 flex-shrink-0">
                                                                @else
                                                                    <div class="w-8 h-8 rounded-full bg-slate-800 text-emerald-400 text-xs font-bold flex items-center justify-center flex-shrink-0">🟢</div>
                                                                @endif
                                                                <div>
                                                                    <span class="text-[10px] font-black text-emerald-400 uppercase">MASUK:</span>
                                                                    <span class="text-xs font-bold text-white block truncate">{{ $ev['player_name'] ?: 'Pemain Masuk' }}</span>
                                                                </div>
                                                            </div>
                                                            @if(!empty($ev['related_player_name']))
                                                                <div class="flex items-center gap-2.5 opacity-75">
                                                                    @if(!empty($ev['related_player_image']))
                                                                        <img src="{{ $ev['related_player_image'] }}" alt="{{ $ev['related_player_name'] }}" class="w-7 h-7 rounded-full object-cover border border-red-500 flex-shrink-0">
                                                                    @else
                                                                        <div class="w-7 h-7 rounded-full bg-slate-800 text-red-400 text-xs font-bold flex items-center justify-center flex-shrink-0">🔴</div>
                                                                    @endif
                                                                    <div>
                                                                        <span class="text-[9px] font-bold text-red-400 uppercase">KELUAR:</span>
                                                                        <span class="text-[11px] font-semibold text-slate-300 block truncate">{{ $ev['related_player_name'] }}</span>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    {{-- Goal Scorer & Assist --}}
                                                    @elseif($isGoal)
                                                        <div class="flex items-center gap-3 pt-1">
                                                            @if(!empty($ev['player_image']))
                                                                <img src="{{ $ev['player_image'] }}" alt="{{ $ev['player_name'] }}" class="w-10 h-10 rounded-full object-cover border-2 border-blue-400 shadow-md flex-shrink-0">
                                                            @else
                                                                <div class="w-10 h-10 rounded-full bg-slate-800 text-white flex items-center justify-center text-base flex-shrink-0">⚽</div>
                                                            @endif
                                                            <div>
                                                                <span class="text-sm font-extrabold text-white block">{{ $ev['player_name'] ?: 'Pencetak Gol' }}</span>
                                                                @if(!empty($ev['related_player_name']))
                                                                    <p class="text-[11px] text-slate-400 mt-0.5">
                                                                        👟 Assist: <strong class="text-slate-200">{{ $ev['related_player_name'] }}</strong>
                                                                    </p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    {{-- Cards --}}
                                                    @else
                                                        <span class="text-xs font-bold text-white block">{{ $ev['player_name'] ?: 'Pemain #' . ($ev['player_id'] ?? '') }}</span>
                                                    @endif

                                                    @if(!empty($ev['info']))
                                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold bg-slate-900 text-slate-300 px-2.5 py-0.5 rounded-full border border-slate-800 mt-1">
                                                            <svg viewBox="0 0 24 24" fill="none" class="w-3 h-3 text-slate-400 shrink-0"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.6"/><path d="M12 11v5M12 8h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                                            {{ $ev['info'] }}
                                                        </span>
                                                    @endif
                                                </div>

                                                @if(!empty($ev['result']))
                                                    <span class="font-mono font-black text-xs bg-blue-500 text-slate-950 px-2.5 py-1 rounded-lg shadow-sm">
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
                    <p class="text-center py-12 text-slate-400 text-sm">Belum ada data event / insiden pada pertandingan ini.</p>
                @endif
            </div>

        </div>

        {{-- RIGHT COLUMN: SCORES BREAKDOWN & MATCH STATS --}}
        <div class="lg:col-span-4 space-y-8">

            {{-- PERIOD SCORES BREAKDOWN (1ST_HALF, 2ND_HALF, CURRENT) --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl">
                <div class="flex items-center gap-3 mb-4">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                        <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5"><rect x="4" y="5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M4 9.5h16M12 9.5V19" stroke="currentColor" stroke-width="1.6"/></svg>
                    </span>
                    <div>
                        <span class="kicker block text-[10px] font-bold uppercase text-emerald-400">Skor Per Babak</span>
                        <h3 class="text-base font-black text-white leading-tight">Rincian Skor</h3>
                    </div>
                </div>

                @if(count($scores) > 0)
                    <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-950/60">
                        <table class="w-full text-center text-xs font-semibold">
                            <thead class="bg-slate-900 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-800 text-[11px]">
                                <tr>
                                    <th class="py-3 px-3 text-emerald-400">{{ $homeTeam['short_code'] ?? 'Home' }}</th>
                                    <th class="py-3 px-3 text-slate-400">Babak</th>
                                    <th class="py-3 px-3 text-blue-400">{{ $awayTeam['short_code'] ?? 'Away' }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/80 text-slate-200 font-medium">
                                @foreach($scores as $sc)
                                    <tr class="hover:bg-slate-800/40 transition-colors {{ $sc['description'] === 'CURRENT' ? 'bg-slate-900/80 font-black' : '' }}">
                                        <td class="py-3 px-3 text-sm font-mono font-bold text-white">{{ $sc['home_goals'] }}</td>
                                        <td class="py-3 px-3 text-slate-400 font-bold text-[11px]">
                                            @if($sc['description'] === '1ST_HALF') Babak 1 (HT)
                                            @elseif($sc['description'] === '2ND_HALF') Babak 2 (FT)
                                            @elseif($sc['description'] === 'CURRENT') Skor Akhir
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
                    <p class="text-xs text-slate-400 text-center py-6">Belum ada rincian skor per babak.</p>
                @endif
            </div>

            {{-- MATCH STATISTICS COMPARISON --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl">
                <div class="flex items-center gap-3 mb-6 pb-3 border-b border-slate-800">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                        <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5"><path d="M5 19V11M12 19V6M19 19v-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </span>
                    <div>
                        <span class="kicker block text-[10px] font-bold uppercase text-emerald-400">Perbandingan Tim</span>
                        <h3 class="text-base font-black text-white leading-tight">Statistik Pertandingan</h3>
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
                                    <span class="text-emerald-400 font-mono text-sm font-black">{{ $st['home_text'] }}</span>
                                    <span class="text-slate-400 text-[11px] uppercase tracking-wider">{{ $st['type_name'] }}</span>
                                    <span class="text-blue-400 font-mono text-sm font-black">{{ $st['away_text'] }}</span>
                                </div>
                                <div class="h-2 w-full bg-slate-950 rounded-full flex overflow-hidden border border-slate-800">
                                    <div class="bg-emerald-500 h-full transition-all" style="width: {{ $hPct }}%"></div>
                                    <div class="bg-blue-500 h-full transition-all" style="width: {{ $aPct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-400 text-center py-8">Belum ada statistik lengkap pertandingan ini.</p>
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
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
         style="display: none;">
        
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-6 relative overflow-hidden"
             @click.away="showModal = false">
            
            {{-- Background Glow --}}
            <div class="absolute -right-16 -top-16 w-60 h-60 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

            {{-- Modal Header with Player Bio --}}
            <div class="flex items-center justify-between pb-4 border-b border-slate-800 relative z-10">
                <div class="flex items-center gap-4">
                    <template x-if="selectedPlayer.player_image">
                        <img :src="selectedPlayer.player_image" :alt="selectedPlayer.player_name" class="w-16 h-16 rounded-2xl object-cover border-2 border-emerald-500 shadow-xl bg-slate-950 flex-shrink-0">
                    </template>
                    <template x-if="!selectedPlayer.player_image">
                        <div class="w-16 h-16 rounded-2xl bg-slate-950 border-2 border-emerald-500 flex items-center justify-center text-emerald-400 shadow-xl flex-shrink-0">
                            <svg viewBox="0 0 24 24" fill="none" class="w-8 h-8"><circle cx="12" cy="8.5" r="3.8" stroke="currentColor" stroke-width="1.6"/><path d="M5 19.5c0-3.6 3.1-5.5 7-5.5s7 1.9 7 5.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                        </div>
                    </template>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-mono font-black px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30" x-text="'No. ' + (selectedPlayer.jersey_number || '-')"></span>
                            <span class="text-xs text-slate-400 font-bold" x-text="selectedPlayer.team_name"></span>
                        </div>
                        <h3 class="text-lg font-black text-white mt-1" x-text="selectedPlayer.player_name"></h3>
                        <span class="text-xs text-slate-400 font-semibold" x-text="'Posisi: ' + (selectedPlayer.position_name || 'Pemain')"></span>
                    </div>
                </div>

                <button @click="showModal = false" class="text-slate-500 hover:text-white p-2 rounded-xl bg-slate-950 border border-slate-800 hover:border-slate-700 transition-colors">
                    <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                </button>
            </div>

            {{-- Match Incidents Badges List in Modal --}}
            <template x-if="selectedPlayer.events && selectedPlayer.events.length > 0">
                <div class="space-y-2 relative z-10">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-400">Insiden / Catatan Pertandingan:</h4>
                    <div class="flex flex-wrap items-center gap-2">
                        <template x-for="evText in selectedPlayer.events" :key="evText">
                            <span class="px-3 py-1 rounded-xl bg-slate-950 text-slate-200 font-bold text-xs border border-slate-800 flex items-center gap-1 shadow-sm" x-text="evText"></span>
                        </template>
                    </div>
                </div>
            </template>

            {{-- In-Match Player Statistics Grid --}}
            <div class="space-y-3 relative z-10">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Statistik Pertandingan Ini:</h4>
                    <template x-if="selectedPlayer.rating">
                        <span class="px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black text-xs border border-amber-500/30 flex items-center gap-1.5">
                            <svg viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5"><path d="M12 3.5l2.5 5.1 5.6.8-4.05 3.95.96 5.6L12 16.9l-5.01 2.65.96-5.6L3.9 9.4l5.6-.8L12 3.5z"/></svg>
                            Rating: <span x-text="selectedPlayer.rating"></span>
                        </span>
                    </template>
                </div>

                <template x-if="selectedPlayer.stats && selectedPlayer.stats.length > 0">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 max-h-60 overflow-y-auto pr-1">
                        <template x-for="stat in selectedPlayer.stats" :key="stat.type_id">
                            <div class="bg-slate-950/80 border border-slate-800/80 p-3 rounded-2xl flex flex-col justify-between">
                                <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider truncate" x-text="stat.type_name"></span>
                                <span class="text-base font-mono font-black text-emerald-400 mt-1" x-text="stat.value"></span>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="!selectedPlayer.stats || selectedPlayer.stats.length === 0">
                    <div class="bg-slate-950/60 border border-slate-800 rounded-2xl p-6 text-center text-slate-400 text-xs">
                        <p>Statistik detail match untuk pemain ini sedang diproses atau belum dicatat.</p>
                    </div>
                </template>
            </div>

            {{-- Modal Action Link to Full Player Profile --}}
            <div class="pt-2 flex items-center justify-end gap-3 relative z-10">
                <button @click="showModal = false" class="px-4 py-2.5 rounded-xl bg-slate-950 text-slate-300 font-bold text-xs hover:bg-slate-800 transition-colors">
                    Tutup
                </button>
                <a :href="'{{ url('/football/players') }}/' + selectedPlayer.player_id" 
                   class="px-5 py-2.5 rounded-xl bg-emerald-500 text-slate-950 font-extrabold text-xs shadow-lg shadow-emerald-500/25 hover:bg-emerald-400 transition-colors flex items-center gap-1.5">
                    <span>Lihat Profil Lengkap</span> &rarr;
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
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl">
            <div class="flex items-center gap-3 mb-4">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                    <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5"><path d="M4 15l5-5 3.5 3.5L20 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 6h5v5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <div>
                    <span class="kicker block text-[10px] font-bold uppercase text-emerald-400">Prediksi Model</span>
                    <h3 class="text-base font-black text-white leading-tight">Prediksi Hasil</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Probabilitas menang / seri / kalah &mdash; model Sportmonks.</p>
                </div>
            </div>
            <div class="flex h-4 w-full overflow-hidden rounded-full border border-slate-800 bg-slate-950">
                <div class="bg-emerald-500 h-full" style="width: {{ $pHome }}%" title="Menang {{ $home_team['name'] ?? 'Home' }}"></div>
                <div class="bg-slate-600 h-full" style="width: {{ $pDraw ?? 0 }}%" title="Seri"></div>
                <div class="bg-red-500 h-full" style="width: {{ $pAway }}%" title="Menang {{ $away_team['name'] ?? 'Away' }}"></div>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs font-bold">
                <span class="text-emerald-400">{{ $home_team['name'] ?? 'Home' }} <span class="font-mono">{{ round($pHome) }}%</span></span>
                <span class="text-slate-400">Seri <span class="font-mono">{{ round($pDraw ?? 0) }}%</span></span>
                <span class="text-red-400"><span class="font-mono">{{ round($pAway) }}%</span> {{ $away_team['name'] ?? 'Away' }}</span>
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
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                        <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.6"/><path d="M12 7v10M14.5 9.3c0-1.2-1.1-1.9-2.5-1.9s-2.5.7-2.5 1.8c0 2.5 5 1.3 5 3.9 0 1.2-1.1 2-2.5 2s-2.5-.8-2.5-2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </span>
                    <div>
                        <span class="kicker block text-[10px] font-bold uppercase text-emerald-400">Pasar 1X2</span>
                        <h3 class="text-base font-black text-white leading-tight">Odds Pra-Laga</h3>
                    </div>
                </div>
                @if($bookName)
                    <span class="text-[11px] font-bold text-slate-500 font-mono self-start">{{ $bookName }}</span>
                @endif
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div class="rounded-2xl border border-slate-800 bg-slate-950 p-4 text-center">
                    <span class="block text-[10px] uppercase font-bold text-slate-500 truncate">{{ $home_team['name'] ?? '1' }}</span>
                    <span class="mt-1 block text-xl font-black font-mono text-emerald-400">{{ $oHome ?? '-' }}</span>
                </div>
                <div class="rounded-2xl border border-slate-800 bg-slate-950 p-4 text-center">
                    <span class="block text-[10px] uppercase font-bold text-slate-500">Seri</span>
                    <span class="mt-1 block text-xl font-black font-mono text-slate-200">{{ $oDraw ?? '-' }}</span>
                </div>
                <div class="rounded-2xl border border-slate-800 bg-slate-950 p-4 text-center">
                    <span class="block text-[10px] uppercase font-bold text-slate-500 truncate">{{ $away_team['name'] ?? '2' }}</span>
                    <span class="mt-1 block text-xl font-black font-mono text-red-400">{{ $oAway ?? '-' }}</span>
                </div>
            </div>
            <p class="mt-3 text-[10px] text-slate-600">Odds hanya untuk informasi. Bukan ajakan bertaruh.</p>
        </div>
    @endif

    {{-- HEAD-TO-HEAD — riwayat pertemuan (proxy live) --}}
    @if(!empty($h2h))
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                    <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5"><path d="M14.5 4H20v5.5M20 4l-8.5 8.5M9.5 20H4v-5.5M4 20l8.5-8.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 14l5 5M9 14l-5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                </span>
                <div>
                    <span class="kicker block text-[10px] font-bold uppercase text-emerald-400">Riwayat Pertemuan</span>
                    <h3 class="text-base font-black text-white leading-tight">Head-to-Head</h3>
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
