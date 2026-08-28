@extends('layouts.app')

@section('title', ($fixture['name'] ?? 'Pertandingan') . ' - Match Center')

@section('content')
<div class="space-y-8" x-data="matchCenter()">

    {{-- Breadcrumb & Back --}}
    <div class="flex items-center justify-between">
        <a href="{{ url()->previous() ?? route('football.index') }}" class="text-xs font-bold text-slate-400 hover:text-emerald-400 transition-colors flex items-center gap-1.5 bg-slate-900 border border-slate-800 px-3.5 py-2 rounded-xl">
            &larr; Kembali ke Jadwal & Klasemen
        </a>
        <div class="flex items-center gap-2 text-xs text-slate-400 font-semibold">
            <span>{{ $league['name'] ?? 'Liga' }}</span>
            <span>•</span>
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
                    {{ $fixture['starting_at'] ? date('l, d F Y • H:i', strtotime($fixture['starting_at'])) : 'Jadwal Ditentukan' }}
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
                                <div class="w-20 h-20 rounded-2xl bg-slate-800 flex items-center justify-center text-4xl mx-auto shadow-inner">🛡️</div>
                            @endif
                            <h2 class="text-xl sm:text-2xl font-black tracking-tight mt-3 text-white group-hover:text-emerald-400 transition-colors">{{ $homeName }}</h2>
                        </a>
                    @else
                        <div class="w-20 h-20 rounded-2xl bg-slate-800 flex items-center justify-center text-4xl mx-auto">🛡️</div>
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
                                <div class="w-20 h-20 rounded-2xl bg-slate-800 flex items-center justify-center text-4xl mx-auto shadow-inner">🛡️</div>
                            @endif
                            <h2 class="text-xl sm:text-2xl font-black tracking-tight mt-3 text-white group-hover:text-emerald-400 transition-colors">{{ $awayName }}</h2>
                        </a>
                    @else
                        <div class="w-20 h-20 rounded-2xl bg-slate-800 flex items-center justify-center text-4xl mx-auto">🛡️</div>
                        <h2 class="text-xl sm:text-2xl font-black tracking-tight mt-3 text-white">{{ $awayName }}</h2>
                    @endif
                    <span class="text-[11px] text-blue-400 font-bold uppercase tracking-wider bg-blue-950/60 px-3 py-0.5 rounded-full border border-blue-800/40">Tim Tamu</span>
                </div>
            </div>

            {{-- Match Meta (Venue & Referee) --}}
            @if($venue || count($referees) > 0)
                <div class="mt-10 pt-5 border-t border-slate-800/80 flex flex-wrap items-center justify-center gap-8 text-xs text-slate-400 font-medium">
                    @if($venue)
                        <span class="flex items-center gap-1.5">📍 <strong class="text-slate-200">Stadion:</strong> {{ $venue['name'] }} ({{ $venue['city_name'] ?? '' }})</span>
                    @endif
                    @if(count($referees) > 0)
                        <span class="flex items-center gap-1.5">⚖️ <strong class="text-slate-200">Wasit:</strong> {{ $referees[0]['name'] ?? 'Wasit Pertandingan' }}</span>
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
                    <div>
                        <h3 class="text-lg font-black text-white flex items-center gap-2">
                            🏟️ Formasi 22 Pemain di Lapangan
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Avatar pemain menampilkan badge insiden (⚽ Gol, 👟 Assist, 🟨/🟥 Kartu, 🔄 Sub). Klik pemain untuk statistik match.</p>
                    </div>

                    {{-- Formations Indicator --}}
                    <div class="flex items-center gap-3 text-xs font-bold">
                        <span class="px-3 py-1 rounded-xl bg-emerald-950 text-emerald-300 border border-emerald-800 flex items-center gap-1.5">
                            <span>🛡️ {{ $homeName }}:</span>
                            <span class="font-mono font-black">{{ $homeFormation }}</span>
                        </span>
                        <span class="px-3 py-1 rounded-xl bg-blue-950 text-blue-300 border border-blue-800 flex items-center gap-1.5">
                            <span>🛡️ {{ $awayName }}:</span>
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
                            <span class="text-[10px] font-mono font-black uppercase tracking-widest text-emerald-300 bg-slate-950/80 px-3 py-1 rounded-full border border-emerald-500/30 shadow-md">
                                🟢 {{ $homeName }} ({{ $homeFormation }})
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
                            <span class="text-[10px] font-mono font-black uppercase tracking-widest text-blue-300 bg-slate-950/80 px-3 py-1 rounded-full border border-blue-500/30 shadow-md">
                                🔵 {{ $awayName }} ({{ $awayFormation }})
                            </span>
                        </div>
                    </div>

                </div>

                {{-- BOTH TEAMS BENCH LISTS --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                    {{-- Home Bench --}}
                    <div class="bg-slate-950/80 border border-slate-800 rounded-2xl p-4 space-y-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-400 flex items-center gap-1.5">
                            <span>🛡️</span> Cadangan {{ $homeName }}
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
                        <span class="text-xs font-bold uppercase tracking-wider text-blue-400 flex items-center gap-1.5">
                            <span>🛡️</span> Cadangan {{ $awayName }}
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
                <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                    <h3 class="text-lg font-black text-white flex items-center gap-2">
                        ⏱️ Garis Waktu Pertandingan (Timeline)
                    </h3>
                    <div class="flex items-center gap-4 text-xs font-bold">
                        <span class="text-emerald-400">👈 {{ $homeName }}</span>
                        <span class="text-slate-600">|</span>
                        <span class="text-blue-400">{{ $awayName }} 👉</span>
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
                                                        <span class="inline-block text-[10px] font-bold bg-slate-900 text-slate-300 px-2.5 py-0.5 rounded-full border border-slate-800 mt-1">
                                                            ℹ️ {{ $ev['info'] }}
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
                                                        <span class="inline-block text-[10px] font-bold bg-slate-900 text-slate-300 px-2.5 py-0.5 rounded-full border border-slate-800 mt-1">
                                                            ℹ️ {{ $ev['info'] }}
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
                <h3 class="text-base font-black text-white mb-4 flex items-center gap-2">
                    📊 Rincian Skor Babak
                </h3>

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
                <div class="flex items-center justify-between mb-6 pb-3 border-b border-slate-800">
                    <h3 class="text-base font-black text-white flex items-center gap-2">
                        📈 Statistik Pertandingan
                    </h3>
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
                        <div class="w-16 h-16 rounded-2xl bg-slate-950 border-2 border-emerald-500 flex items-center justify-center text-2xl shadow-xl flex-shrink-0">
                            👤
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

                <button @click="showModal = false" class="text-slate-500 hover:text-white p-2 rounded-xl bg-slate-950 border border-slate-800 text-lg transition-colors">
                    ✕
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
                        <span class="px-2.5 py-1 rounded-xl bg-amber-500/20 text-amber-300 font-mono font-black text-xs border border-amber-500/30 flex items-center gap-1">
                            ⭐️ Rating: <span x-text="selectedPlayer.rating"></span>
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
